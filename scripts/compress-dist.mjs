/**
 * Преджатие собранных ассетов: рядом с каждым файлом кладутся .br и .gz.
 *
 * Смысл в том, чтобы сервер отдавал готовый сжатый файл вместо того, чтобы
 * жать его на лету при каждом запросе. Brotli на максимальном уровне заметно
 * выигрывает у gzip, но жмёт медленно — на этапе сборки это неважно.
 *
 * ВАЖНО: сами по себе эти файлы ничего не ускоряют. Их должен отдавать
 * веб-сервер: nginx — brotli_static on / gzip_static on, Apache — правила
 * mod_rewrite (пример в README). Если отдача не настроена, файлы просто лежат
 * мёртвым грузом и ни на что не влияют.
 *
 * Отдельного пруна нет: Vite собран с emptyOutDir, поэтому dist перед каждой
 * сборкой очищается и устаревшие .br/.gz не переживают её физически.
 */

import { readdir, readFile, writeFile, stat } from "node:fs/promises";
import { existsSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { promisify } from "node:util";
import { brotliCompress, constants, gzip } from "node:zlib";
import path from "node:path";

const gzipAsync = promisify(gzip);
const brotliAsync = promisify(brotliCompress);

const ROOT = fileURLToPath(new URL("..", import.meta.url));
const DIST = path.join(ROOT, "assets", "dist");

/** Что жмём. Карты исходников нужны только разработчику — их пропускаем. */
const COMPRESSIBLE = new Set([".css", ".js", ".svg", ".json", ".ico"]);

/** Ниже этого размера накладные расходы съедают выигрыш. */
const MIN_BYTES = 1024;

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

function formatKb(bytes) {
	return `${(bytes / 1024).toFixed(2)} kB`;
}

async function main() {
	if (!existsSync(DIST)) {
		console.log("assets/dist/ не найдена — сначала соберите проект.");
		return;
	}

	const files = (await walk(DIST)).filter((rel) => {
		if (rel.endsWith(".map") || rel.endsWith(".br") || rel.endsWith(".gz")) return false;
		return COMPRESSIBLE.has(path.extname(rel).toLowerCase());
	});

	let raw = 0;
	let br = 0;
	let gz = 0;
	let count = 0;

	for (const rel of files) {
		const full = path.join(DIST, rel);
		const { size } = await stat(full);

		if (size < MIN_BYTES) continue;

		const source = await readFile(full);

		const [brotli, gzipped] = await Promise.all([
			brotliAsync(source, {
				params: {
					[constants.BROTLI_PARAM_QUALITY]: constants.BROTLI_MAX_QUALITY,
					[constants.BROTLI_PARAM_SIZE_HINT]: size,
				},
			}),
			gzipAsync(source, { level: constants.Z_BEST_COMPRESSION }),
		]);

		await Promise.all([writeFile(`${full}.br`, brotli), writeFile(`${full}.gz`, gzipped)]);

		raw += size;
		br += brotli.length;
		gz += gzipped.length;
		count++;
	}

	if (!count) {
		console.log("compress: нечего жать (всё мельче 1 kB)");
		return;
	}

	console.log(
		`compress: ${count} файлов — ${formatKb(raw)} → br ${formatKb(br)}, gz ${formatKb(gz)}`,
	);
}

main().catch((error) => {
	console.error(error);
	process.exitCode = 1;
});
