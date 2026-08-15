import { defineConfig } from 'vite';

export default defineConfig({
	// Относительные URL обязательны: тема живёт в /wp-content/themes/<имя>/,
	// а base по умолчанию ('/') превратил бы ссылку на ассет из CSS в /img/foo.jpg
	// — то есть в 404. С './' пути считаются от собранного файла и переживают
	// и переименование темы, и установку WordPress в подкаталог.
	base: './',
	build: {
		outDir: 'assets/dist',
		emptyOutDir: true,
		// Не инлайнить мелкие ассеты в base64: иначе для image-set() в CSS
		// обе версии (webp и запасная) вшиваются в стили, и смысл развилки теряется.
		assetsInlineLimit: 0,
		rollupOptions: {
			input: 'assets/js/main.js',
			output: {
				assetFileNames: ({ name }) => {
					if (/\.css$/.test(name ?? '')) return 'css/[name][extname]';
					return 'img/[name][extname]';
				},
				entryFileNames: 'js/[name].js',
				chunkFileNames: 'js/[name].js',
			},
		},
		sourcemap: true,
	},
});
