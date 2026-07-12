import { motion } from 'framer-motion';

export const containerVariants = {
    hidden: { opacity: 0 },
    show: { opacity: 1, transition: { staggerChildren: 0.08 } },
};

export const itemVariants = {
    hidden: { opacity: 0, y: 20 },
    show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 24 } },
};

const defaultClassName = 'py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6';

export default function PageMotion({ children, className = defaultClassName }) {
    return (
        <motion.div
            className={className}
            variants={containerVariants}
            initial="hidden"
            animate="show"
        >
            {children}
        </motion.div>
    );
}

export function MotionItem({ children, className = '', ...props }) {
    return (
        <motion.div variants={itemVariants} className={className} {...props}>
            {children}
        </motion.div>
    );
}
