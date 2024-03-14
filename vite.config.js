import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'

export default defineConfig({
	plugins: [
		laravel({
			input: [
				'resources/js/Init.jsx',
				'resources/js/access.js',
				'resources/css/addon.css',
			],
			publicDirectory: 'resources/dist',
		}),
		react(),
	],
	build: {
		rollupOptions: {
			plugins: [
				{
					name: 'wrap-iife',
					generateBundle(options, bundle) {
						for (const chunk of Object.values(bundle)) {
							if (chunk.code) {
								chunk.code = `(function(){\n${chunk.code}\n})()`
							}
						}
					},
				},
			],
		},
	},
})
