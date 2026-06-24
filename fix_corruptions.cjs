const fs = require('fs');
const path = require('path');

const replacements = [
    // Fix trailing 0 for 500 colors
    { pattern: /bg-indigo-50 dark:bg-indigo-900\/200/g, replacement: 'bg-indigo-500' },
    { pattern: /bg-rose-50 dark:bg-rose-900\/200/g, replacement: 'bg-rose-500' },
    { pattern: /bg-emerald-50 dark:bg-emerald-900\/200/g, replacement: 'bg-emerald-500' },
    { pattern: /bg-amber-50 dark:bg-amber-900\/200/g, replacement: 'bg-amber-500' },
    { pattern: /bg-slate-50 dark:bg-slate-8000/g, replacement: 'bg-slate-500' },
    { pattern: /bg-gray-50 dark:bg-slate-8000/g, replacement: 'bg-gray-500' },
    
    // Fix opacities broken by the space injection
    { pattern: /bg-white dark:bg-slate-900\/(\d+)/g, replacement: 'bg-white/$1 dark:bg-slate-900/$1' },
    { pattern: /bg-slate-50 dark:bg-slate-800\/(\d+)/g, replacement: 'bg-slate-50/$1 dark:bg-slate-800/$1' },
    { pattern: /bg-gray-50 dark:bg-slate-800\/(\d+)/g, replacement: 'bg-gray-50/$1 dark:bg-slate-800/$1' },

    { pattern: /text-slate-900 dark:text-white\/(\d+)/g, replacement: 'text-slate-900/$1 dark:text-white/$1' },
    { pattern: /text-gray-900 dark:text-white\/(\d+)/g, replacement: 'text-gray-900/$1 dark:text-white/$1' },
    { pattern: /text-slate-800 dark:text-slate-200\/(\d+)/g, replacement: 'text-slate-800/$1 dark:text-slate-200/$1' },
    { pattern: /text-gray-800 dark:text-slate-200\/(\d+)/g, replacement: 'text-gray-800/$1 dark:text-slate-200/$1' },
    { pattern: /text-slate-700 dark:text-slate-300\/(\d+)/g, replacement: 'text-slate-700/$1 dark:text-slate-300/$1' },
    { pattern: /text-gray-700 dark:text-slate-300\/(\d+)/g, replacement: 'text-gray-700/$1 dark:text-slate-300/$1' },
    { pattern: /text-slate-600 dark:text-slate-400\/(\d+)/g, replacement: 'text-slate-600/$1 dark:text-slate-400/$1' },
    { pattern: /text-gray-600 dark:text-slate-400\/(\d+)/g, replacement: 'text-gray-600/$1 dark:text-slate-400/$1' },
    { pattern: /text-slate-500 dark:text-slate-400\/(\d+)/g, replacement: 'text-slate-500/$1 dark:text-slate-400/$1' },
    { pattern: /text-gray-500 dark:text-slate-400\/(\d+)/g, replacement: 'text-gray-500/$1 dark:text-slate-400/$1' },

    { pattern: /border-slate-200 dark:border-slate-700\/(\d+)/g, replacement: 'border-slate-200/$1 dark:border-slate-700/$1' },
    { pattern: /border-gray-200 dark:border-slate-700\/(\d+)/g, replacement: 'border-gray-200/$1 dark:border-slate-700/$1' },
    { pattern: /border-slate-100 dark:border-slate-800\/(\d+)/g, replacement: 'border-slate-100/$1 dark:border-slate-800/$1' },
    { pattern: /border-gray-100 dark:border-slate-800\/(\d+)/g, replacement: 'border-gray-100/$1 dark:border-slate-800/$1' },

    { pattern: /divide-slate-200 dark:divide-slate-700\/(\d+)/g, replacement: 'divide-slate-200/$1 dark:divide-slate-700/$1' },
    { pattern: /divide-gray-200 dark:divide-slate-700\/(\d+)/g, replacement: 'divide-gray-200/$1 dark:divide-slate-700/$1' },
    { pattern: /divide-slate-100 dark:divide-slate-800\/(\d+)/g, replacement: 'divide-slate-100/$1 dark:divide-slate-800/$1' },
    { pattern: /divide-gray-100 dark:divide-slate-800\/(\d+)/g, replacement: 'divide-gray-100/$1 dark:divide-slate-800/$1' },

    { pattern: /text-indigo-600 dark:text-indigo-400\/(\d+)/g, replacement: 'text-indigo-600/$1 dark:text-indigo-400/$1' },
    { pattern: /text-rose-600 dark:text-rose-400\/(\d+)/g, replacement: 'text-rose-600/$1 dark:text-rose-400/$1' },
    { pattern: /text-emerald-600 dark:text-emerald-400\/(\d+)/g, replacement: 'text-emerald-600/$1 dark:text-emerald-400/$1' },
    { pattern: /text-amber-600 dark:text-amber-400\/(\d+)/g, replacement: 'text-amber-600/$1 dark:text-amber-400/$1' }
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
console.log(`Total files fixed: ${count}`);
