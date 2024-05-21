module.exports = {
	content: ['./resources/**/*'],
	corePlugins: {
		preflight: false,
	},
	prefix: 'p-',
	important: true,
	screens: {
		xs: '480px',
		sm: '640px',
		md: '768px',
	},
	darkMode: [
		'variant',
		['@media (prefers-color-scheme: dark) { & }', 'html[class*="dark"] &'],
	],
}
