const fs = require('fs');
const path = require('path');

const replacements = [
    { pattern: /hover:bg-indigo-50 dark:bg-indigo-900\/20\/30/g, replacement: 'hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20' },
    { pattern: /hover:bg-emerald-50 dark:bg-emerald-900\/20\/30/g, replacement: 'hover:bg-emerald-50/30 dark:hover:bg-emerald-900/20' },
    { pattern: /hover:bg-rose-50 dark:bg-rose-900\/20\/30/g, replacement: 'hover:bg-rose-50/30 dark:hover:bg-rose-900/20' },
    { pattern: /hover:bg-amber-50 dark:bg-amber-900\/20\/30/g, replacement: 'hover:bg-amber-50/30 dark:hover:bg-amber-900/20' }
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
