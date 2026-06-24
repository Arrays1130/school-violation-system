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
                'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-sm font-medium text-slate-800 dark:text-slate-200 placeholder-slate-400 ' +
                className
            }
            ref={input}
        />
    );
});
