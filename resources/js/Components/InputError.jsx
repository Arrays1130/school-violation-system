import React from 'react';

export default function InputError({ message, className = '', id, ...props }) {
    return message ? (
        <p id={id} role="alert" {...props} className={'text-sm text-red-600 ' + className}>
            {message}
        </p>
    ) : null;
}
