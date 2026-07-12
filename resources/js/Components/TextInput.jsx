import React, { forwardRef, useEffect, useRef } from 'react';

export default forwardRef(function TextInput({ type = 'text', className = '', isFocused = false, ...props }, ref) {
    const input = ref ? ref : useRef();

    useEffect(() => {
        if (isFocused) {
            input.current.focus();
        }
    }, []);

    return (
        <input
            {...props}
            type={type}
            className={
                'w-full border border-slate-300 bg-white px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-sm font-medium text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 ' +
                className
            }
            ref={input}
        />
    );
});
