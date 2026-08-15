/**
 * Конвертация картинок темы в WebP.
 *
 *   assets/images/  — исходники (коммитятся, правятся руками)
 *   assets/webp/    — результат конвертации, зеркалит структуру исходников
 *
 * Рядом кладётся assets/webp/manifest.json с размерами каждой картинки:
 * из него PHP берёт width/height, чтобы не дёргать getimagesize() на каждый
 * рендер и чтобы вёрстка не прыгала при загрузке.
 *
 * Конвертация инкрементальная — пересобирается только то, что новее своего webp.
 * Осиротевшие webp (исходник удалили) подчищаются.
 *
 * Картинки из медиатеки WordPress эта сборка не трогает — только те, что
 * лежат внутри темы.
 */

import { readdir, mkdir, stat, writeFile, unlink } from "node:fs/promises";
import { existsSync } from "node:fs";
import { fileURLToPath } from "node:url";
import path from "node:path";
import sharp from "sharp";

const ROOT = fileURLToPath(new URL("..", import.meta.url));
const SRC = path.join(ROOT, "assets", "images");
const OUT = path.join(ROOT, "assets", "webp");
const MANIFEST = path.join(OUT, "manifest.json");

/** Форматы, которые конвертируем в webp. */
const CONVERTIBLE = new Set([".jpg", ".jpeg", ".png"]);
/** Форматы, которые попадают в манифест (ради размеров), но не конвертируются. */
const PASSTHROUGH = new Set([".svg", ".gif", ".webp", ".avif"]);

const QUALITY = 82;

/** Рекурсивный обход директории, возвращает пути относительно неё. */
async function walk(dir, base = dir) {
	if (!existsSync(dir)) return [];

	const entries = await readdir(dir, { withFileTypes: true });
	const files = [];

	for (const entry of entries) {
		const full = path.join(dir, entry.name);
		if (entry.isDirectory()) {
			files.push(...(await walk(full, base)));
		} else if (entry.isFile()) {
			files.push(path.relative(base, full).split(path.sep).join("/"));
		}
	}

	return files;
}

/** Нужно ли пересобирать: нет результата или исходник новее. */
async function isStale(src, out) {
	if (!existsSync(out)) return true;

	const [a, b] = await Promise.all([stat(src), stat(out)]);
	return a.mtimeMs > b.mtimeMs;
}

function formatKb(bytes) {
	return `${(bytes / 1024).toFixed(1)} kB`;
}

async function main() {
	if (!existsSync(SRC)) {
		console.log("assets/images/ не найдена — конвертировать нечего.");
		return;
	}

	const sources = (await walk(SRC)).filter((f) => {
		const ext = path.extname(f).toLowerCase();
		return CONVERTIBLE.has(ext) || PASSTHROUGH.has(ext);
	});

	// Раннего выхода на пустом списке нет намеренно: если удалить последний
	// исходник, дальше должны отработать подчистка сирот и перезапись манифеста.
	await mkdir(OUT, { recursive: true });

	const manifest = {};
	const expected = new Set(["manifest.json"]);
	let converted = 0;
	let skipped = 0;
	let savedBytes = 0;

	for (const rel of sources) {
		const srcPath = path.join(SRC, rel);
		const ext = path.extname(rel).toLowerCase();

		let width = 0;
		let height = 0;

		try {
			const meta = await sharp(srcPath).metadata();
			width = meta.width ?? 0;
			height = meta.height ?? 0;
		} catch {
			// SVG без явных размеров и битые файлы — просто без width/height.
		}

		if (!CONVERTIBLE.has(ext)) {
			manifest[rel] = { w: width, h: height, webp: null };
			continue;
		}

		const relWebp = rel.replace(/\.(jpe?g|png)$/i, ".webp");
		const outPath = path.join(OUT, relWebp);
		expected.add(relWebp);

		if (await isStale(srcPath, outPath)) {
			await mkdir(path.dirname(outPath), { recursive: true });
			await sharp(srcPath).webp({ quality: QUALITY }).toFile(outPath);
			converted++;
		} else {
			skipped++;
		}

		const [srcStat, outStat] = await Promise.all([stat(srcPath), stat(outPath)]);
		savedBytes += srcStat.size - outStat.size;

		manifest[rel] = { w: width, h: height, webp: relWebp };
	}

	// Подчищаем webp, у которых больше нет исходника.
	let pruned = 0;
	for (const rel of await walk(OUT)) {
		if (!expected.has(rel)) {
			await unlink(path.join(OUT, rel));
			pruned++;
		}
	}

	await writeFile(MANIFEST, `${JSON.stringify(manifest, null, "\t")}\n`, "utf8");

	const parts = [`${converted} сконвертировано`, `${skipped} без изменений`];
	if (pruned) parts.push(`${pruned} удалено`);

	console.log(`images: ${parts.join(", ")} — экономия ${formatKb(savedBytes)}`);
}

main().catch((error) => {
	console.error(error);
	process.exitCode = 1;
});
