/**
 * Преджатие отдаваемых файлов: рядом с каждым кладутся .br и .gz.
 *
 * Обрабатываются две директории:
 *   assets/dist   — сборка Vite
 *   assets/vendor — сторонние библиотеки, мимо сборки
 *
 * Смысл в том, чтобы сервер отдавал готовый сжатый файл вместо того, чтобы
 * жать его на лету при каждом запросе. Brotli на максимальном уровне заметно
 * выигрывает у gzip, но жмёт медленно — на этапе сборки это неважно.
 *
 * ВАЖНО: сами по себе эти файлы ничего не ускоряют. Их должен отдавать
 * веб-сервер: nginx — brotli_static on / gzip_static on, Apache — правила
 * mod_rewrite (пример в README). Если отдача не настроена, файлы просто лежат
 * мёртвым грузом и ни на что не влияют.
 */

import { readdir, readFile, stat, unlink, writeFile } from "node:fs/promises";
import { existsSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { promisify } from "node:util";
import { brotliCompress, constants, gzip } from "node:zlib";
import path from "node:path";

const gzipAsync = promisify(gzip);
const brotliAsync = promisify(brotliCompress);

const ROOT = fileURLToPath(new URL("..", import.meta.url));
const TARGETS = [path.join(ROOT, "assets", "dist"), path.join(ROOT, "assets", "vendor")];

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

function isArchive(rel) {
	return rel.endsWith(".br") || rel.endsWith(".gz");
}

function shouldCompress(rel) {
	if (rel.endsWith(".map") || isArchive(rel)) return false;
	return COMPRESSIBLE.has(path.extname(rel).toLowerCase());
}

function formatKb(bytes) {
	return `${(bytes / 1024).toFixed(2)} kB`;
}

async function main() {
	let raw = 0;
	let br = 0;
	let gz = 0;
	let count = 0;
	let pruned = 0;

	for (const target of TARGETS) {
		if (!existsSync(target)) continue;

		const all = await walk(target);
		const sources = all.filter(shouldCompress);

		// assets/dist очищается Vite перед каждой сборкой, а assets/vendor нет:
		// если библиотеку удалили или переименовали, её .br/.gz остались бы висеть.
		const expected = new Set(sources.flatMap((rel) => [`${rel}.br`, `${rel}.gz`]));

		for (const rel of all.filter(isArchive)) {
			if (!expected.has(rel)) {
				await unlink(path.join(target, rel));
				pruned++;
			}
		}

		for (const rel of sources) {
			const full = path.join(target, rel);
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
	}

	if (!count) {
		console.log("compress: нечего жать");
		return;
	}

	const tail = pruned ? `, удалено устаревших ${pruned}` : "";

	console.log(
		`compress: ${count} файлов — ${formatKb(raw)} → br ${formatKb(br)}, gz ${formatKb(gz)}${tail}`,
	);
}

main().catch((error) => {
	console.error(error);
	process.exitCode = 1;
});
