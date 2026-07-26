import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';

export const containerVariants = {
    hidden: { opacity: 0 },
    show: {
        opacity: 1,
        transition: { staggerChildren: 0.07, delayChildren: 0.04 },
    },
};

export const itemVariants = {
    hidden: { opacity: 0, y: 18 },
    show: {
        opacity: 1,
        y: 0,
        transition: { type: 'spring', stiffness: 320, damping: 26 },
    },
};

const reducedContainer = {
    hidden: { opacity: 0 },
    show: { opacity: 1, transition: { duration: 0.15 } },
};

const reducedItem = {
    hidden: { opacity: 0 },
    show: { opacity: 1, transition: { duration: 0.15 } },
};

const defaultClassName = 'py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6';

export default function PageMotion({ children, className = defaultClassName }) {
    const reduceMotion = useReducedMotion();

    return (
        <motion.div
            className={className}
            variants={reduceMotion ? reducedContainer : containerVariants}
            initial="hidden"
            animate="show"
        >
            {children}
        </motion.div>
    );
}

export function MotionItem({ children, className = '', ...props }) {
    const reduceMotion = useReducedMotion();

    return (
        <motion.div
            variants={reduceMotion ? reducedItem : itemVariants}
            className={className}
            {...props}
        >
            {children}
        </motion.div>
    );
}

/** Soft fade/scale for overlays and dialogs. */
export function ModalMotion({ open, children, className = '' }) {
    const reduceMotion = useReducedMotion();

    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    className={className}
                    initial={reduceMotion ? { opacity: 0 } : { opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: reduceMotion ? 0.12 : 0.2 }}
                >
                    {children}
                </motion.div>
            )}
        </AnimatePresence>
    );
}

export function ModalBackdrop({ onClick }) {
    const reduceMotion = useReducedMotion();

    return (
        <motion.div
            className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
            onClick={onClick}
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: reduceMotion ? 0.1 : 0.18 }}
        />
    );
}

export function ModalPanel({ children, className = '' }) {
    const reduceMotion = useReducedMotion();

    return (
        <motion.div
            className={className}
            initial={reduceMotion ? { opacity: 0 } : { opacity: 0, y: 16, scale: 0.97 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={reduceMotion ? { opacity: 0 } : { opacity: 0, y: 10, scale: 0.98 }}
            transition={{ type: 'spring', stiffness: 380, damping: 28 }}
        >
            {children}
        </motion.div>
    );
}
