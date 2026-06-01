import { defineConfig } from 'vite';

export default defineConfig( {
	css: {
		preprocessorOptions: {
			scss: { api: 'modern-compiler' },
		},
	},
	build: {
		outDir: 'assets/dist',
		emptyOutDir: true,
		rollupOptions: {
			input: 'assets/js/main.js',
			output: {
				assetFileNames: ( { name } ) => {
					if ( /\.css$/.test( name ?? '' ) ) return 'css/[name][extname]';
					return 'img/[name][extname]';
				},
				entryFileNames: 'js/[name].js',
				chunkFileNames: 'js/[name].js',
			},
		},
		sourcemap: true,
	},
} );
