/**
 * Renders i-Link brand copy with only the leading "i" in italic lowercase.
 */
export default function BrandText({ children, as: Tag = 'span', className = '', ...props }) {
    const text = typeof children === 'string' ? children : String(children ?? '');

    if (!/^i/i.test(text)) {
        return (
            <Tag className={className} {...props}>
                {text}
            </Tag>
        );
    }

    const rest = text.slice(1);

    return (
        <Tag className={className} {...props}>
            <span className="italic normal-case">i</span>
            {rest}
        </Tag>
    );
}
