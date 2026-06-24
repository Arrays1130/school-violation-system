const fs = require('fs');
const path = require('path');

const replacements = [
    { pattern: /bg-white(?!\s+dark:bg-)/g, replacement: 'bg-white dark:bg-slate-900' },
    { pattern: /bg-slate-50(?!\s+dark:bg-)/g, replacement: 'bg-slate-50 dark:bg-slate-800' },
    { pattern: /bg-gray-50(?!\s+dark:bg-)/g, replacement: 'bg-gray-50 dark:bg-slate-800' },
    { pattern: /text-slate-900(?!\s+dark:text-)/g, replacement: 'text-slate-900 dark:text-white' },
    { pattern: /text-gray-900(?!\s+dark:text-)/g, replacement: 'text-gray-900 dark:text-white' },
    { pattern: /text-slate-800(?!\s+dark:text-)/g, replacement: 'text-slate-800 dark:text-slate-200' },
    { pattern: /text-gray-800(?!\s+dark:text-)/g, replacement: 'text-gray-800 dark:text-slate-200' },
    { pattern: /text-slate-700(?!\s+dark:text-)/g, replacement: 'text-slate-700 dark:text-slate-300' },
    { pattern: /text-gray-700(?!\s+dark:text-)/g, replacement: 'text-gray-700 dark:text-slate-300' },
    { pattern: /text-slate-600(?!\s+dark:text-)/g, replacement: 'text-slate-600 dark:text-slate-400' },
    { pattern: /text-gray-600(?!\s+dark:text-)/g, replacement: 'text-gray-600 dark:text-slate-400' },
    { pattern: /text-slate-500(?!\s+dark:text-)/g, replacement: 'text-slate-500 dark:text-slate-400' },
    { pattern: /text-gray-500(?!\s+dark:text-)/g, replacement: 'text-gray-500 dark:text-slate-400' },
    { pattern: /border-slate-200(?!\s+dark:border-)/g, replacement: 'border-slate-200 dark:border-slate-700' },
    { pattern: /border-gray-200(?!\s+dark:border-)/g, replacement: 'border-gray-200 dark:border-slate-700' },
    { pattern: /border-slate-100(?!\s+dark:border-)/g, replacement: 'border-slate-100 dark:border-slate-800' },
    { pattern: /border-gray-100(?!\s+dark:border-)/g, replacement: 'border-gray-100 dark:border-slate-800' },
    { pattern: /divide-slate-200(?!\s+dark:divide-)/g, replacement: 'divide-slate-200 dark:divide-slate-700' },
    { pattern: /divide-gray-200(?!\s+dark:divide-)/g, replacement: 'divide-gray-200 dark:divide-slate-700' },
    { pattern: /divide-slate-100(?!\s+dark:divide-)/g, replacement: 'divide-slate-100 dark:divide-slate-800' },
    { pattern: /divide-gray-100(?!\s+dark:divide-)/g, replacement: 'divide-gray-100 dark:divide-slate-800' },
    { pattern: /hover:bg-slate-50(?!\s+dark:hover:bg-)/g, replacement: 'hover:bg-slate-50 dark:hover:bg-slate-800' },
    { pattern: /hover:bg-gray-50(?!\s+dark:hover:bg-)/g, replacement: 'hover:bg-gray-50 dark:hover:bg-slate-800' },
    { pattern: /bg-indigo-50(?!\s+dark:bg-)/g, replacement: 'bg-indigo-50 dark:bg-indigo-900/20' },
    { pattern: /text-indigo-600(?!\s+dark:text-)/g, replacement: 'text-indigo-600 dark:text-indigo-400' },
    { pattern: /bg-rose-50(?!\s+dark:bg-)/g, replacement: 'bg-rose-50 dark:bg-rose-900/20' },
    { pattern: /text-rose-600(?!\s+dark:text-)/g, replacement: 'text-rose-600 dark:text-rose-400' },
    { pattern: /bg-emerald-50(?!\s+dark:bg-)/g, replacement: 'bg-emerald-50 dark:bg-emerald-900/20' },
    { pattern: /text-emerald-600(?!\s+dark:text-)/g, replacement: 'text-emerald-600 dark:text-emerald-400' },
    { pattern: /bg-amber-50(?!\s+dark:bg-)/g, replacement: 'bg-amber-50 dark:bg-amber-900/20' },
    { pattern: /text-amber-600(?!\s+dark:text-)/g, replacement: 'text-amber-600 dark:text-amber-400' }
];

let count = 0;

function processDirectory(dir) {
    const files = fs.readdirSync(dir);

    files.forEach(file => {
        const filepath = path.join(dir, file);
        const stat = fs.statSync(filepath);

        if (stat.isDirectory()) {
            processDirectory(filepath);
        } else if (file.endsWith('.jsx')) {
            try {
                let content = fs.readFileSync(filepath, 'utf8');
                let originalContent = content;

                replacements.forEach(({ pattern, replacement }) => {
                    content = content.replace(pattern, replacement);
                });

                if (content !== originalContent) {
                    fs.writeFileSync(filepath, content, 'utf8');
                    console.log(`Updated: ${filepath}`);
                    count++;
                }
            } catch (err) {
                console.error(`Error processing ${filepath}: ${err}`);
            }
        }
    });
}

processDirectory(path.join(__dirname, 'resources', 'js'));
console.log(`Total files updated: ${count}`);
