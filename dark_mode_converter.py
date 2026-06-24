import os
import re

def process_directory(directory):
    replacements = {
        r'bg-white(?!\s+dark:bg-)': 'bg-white dark:bg-slate-900',
        r'bg-slate-50(?!\s+dark:bg-)': 'bg-slate-50 dark:bg-slate-800',
        r'bg-gray-50(?!\s+dark:bg-)': 'bg-gray-50 dark:bg-slate-800',
        
        r'text-slate-900(?!\s+dark:text-)': 'text-slate-900 dark:text-white',
        r'text-gray-900(?!\s+dark:text-)': 'text-gray-900 dark:text-white',
        
        r'text-slate-800(?!\s+dark:text-)': 'text-slate-800 dark:text-slate-200',
        r'text-gray-800(?!\s+dark:text-)': 'text-gray-800 dark:text-slate-200',
        
        r'text-slate-700(?!\s+dark:text-)': 'text-slate-700 dark:text-slate-300',
        r'text-gray-700(?!\s+dark:text-)': 'text-gray-700 dark:text-slate-300',
        
        r'text-slate-600(?!\s+dark:text-)': 'text-slate-600 dark:text-slate-400',
        r'text-gray-600(?!\s+dark:text-)': 'text-gray-600 dark:text-slate-400',
        
        r'text-slate-500(?!\s+dark:text-)': 'text-slate-500 dark:text-slate-400',
        r'text-gray-500(?!\s+dark:text-)': 'text-gray-500 dark:text-slate-400',
        
        r'border-slate-200(?!\s+dark:border-)': 'border-slate-200 dark:border-slate-700',
        r'border-gray-200(?!\s+dark:border-)': 'border-gray-200 dark:border-slate-700',
        r'border-slate-100(?!\s+dark:border-)': 'border-slate-100 dark:border-slate-800',
        r'border-gray-100(?!\s+dark:border-)': 'border-gray-100 dark:border-slate-800',
        
        r'divide-slate-200(?!\s+dark:divide-)': 'divide-slate-200 dark:divide-slate-700',
        r'divide-gray-200(?!\s+dark:divide-)': 'divide-gray-200 dark:divide-slate-700',
        r'divide-slate-100(?!\s+dark:divide-)': 'divide-slate-100 dark:divide-slate-800',
        r'divide-gray-100(?!\s+dark:divide-)': 'divide-gray-100 dark:divide-slate-800',

        r'hover:bg-slate-50(?!\s+dark:hover:bg-)': 'hover:bg-slate-50 dark:hover:bg-slate-800',
        r'hover:bg-gray-50(?!\s+dark:hover:bg-)': 'hover:bg-gray-50 dark:hover:bg-slate-800',
        
        r'bg-indigo-50(?!\s+dark:bg-)': 'bg-indigo-50 dark:bg-indigo-900/20',
        r'text-indigo-600(?!\s+dark:text-)': 'text-indigo-600 dark:text-indigo-400',
        
        r'bg-rose-50(?!\s+dark:bg-)': 'bg-rose-50 dark:bg-rose-900/20',
        r'text-rose-600(?!\s+dark:text-)': 'text-rose-600 dark:text-rose-400',
        
        r'bg-emerald-50(?!\s+dark:bg-)': 'bg-emerald-50 dark:bg-emerald-900/20',
        r'text-emerald-600(?!\s+dark:text-)': 'text-emerald-600 dark:text-emerald-400',
        
        r'bg-amber-50(?!\s+dark:bg-)': 'bg-amber-50 dark:bg-amber-900/20',
        r'text-amber-600(?!\s+dark:text-)': 'text-amber-600 dark:text-amber-400',
    }

    count = 0
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.jsx'):
                filepath = os.path.join(root, file)
                try:
                    with open(filepath, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    original_content = content
                    for pattern, replacement in replacements.items():
                        content = re.sub(pattern, replacement, content)
                    
                    if content != original_content:
                        with open(filepath, 'w', encoding='utf-8') as f:
                            f.write(content)
                        print(f"Updated: {filepath}")
                        count += 1
                except Exception as e:
                    print(f"Error processing {filepath}: {e}")
    print(f"Total files updated: {count}")

if __name__ == "__main__":
    process_directory(r"c:\laragon\www\school violation system\resources\js")
