// Import all modules to start
const loaded = new Set<string>()

import './style.css'

// Match both .ts and .js files
const modules = import.meta.glob('./*.{ts,js}')

// Dynamically tree-shake components
document.querySelectorAll<HTMLElement>('[data-component]').forEach(el => {
    const name = el.dataset.component
    if (!name || loaded.has(name)) return

    // Try both possible file paths
    const tsPath = `./${name}.ts`
    const jsPath = `./${name}.js`

    const moduleLoader = modules[tsPath] || modules[jsPath]

    if (moduleLoader) {
        loaded.add(name)
        moduleLoader()
    }
})