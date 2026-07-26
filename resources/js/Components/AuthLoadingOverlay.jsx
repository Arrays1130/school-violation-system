import React, { useState } from 'react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import { LoaderCircle } from 'lucide-react';
import { asset } from '@/Components/AuthBackground';
import BrandText from '@/Components/BrandText';

export default function AuthLoadingOverlay({ show, message = 'Signing you in…' }) {
    const reduceMotion = useReducedMotion();
    const [logoSrc, setLogoSrc] = useState(asset('brand_logo.png'));

    return (
        <AnimatePresence>
            {show && (
                <motion.div
                    key="auth-loading-overlay"
                    className="fixed inset-0 z-[100] flex items-center justify-center px-4"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: reduceMotion ? 0.12 : 0.25 }}
                    role="status"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <div className="absolute inset-0 bg-[#7eb8e6]/55 backdrop-blur-md" aria-hidden="true" />

                    <motion.div
                        className="relative z-10 flex w-full max-w-sm flex-col items-center rounded-[2rem] border border-white/60 bg-white/80 px-8 py-10 shadow-2xl backdrop-blur-md"
                        initial={reduceMotion ? { opacity: 0 } : { opacity: 0, scale: 0.96, y: 12 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={reduceMotion ? { opacity: 0 } : { opacity: 0, scale: 0.98, y: 8 }}
                        transition={{ duration: reduceMotion ? 0.12 : 0.3, ease: [0.22, 1, 0.36, 1] }}
                    >
                        <div className="mb-5 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-2 border-white bg-blue-100 shadow-sm">
                            <img
                                src={logoSrc}
                                alt=""
                                className="h-16 w-16 object-contain"
                                onError={() => {
                                    setLogoSrc((current) =>
                                        current.includes('brand_logo.png') ? asset('images/logo.png') : current
                                    );
                                }}
                            />
                        </div>

                        <LoaderCircle
                            className={`mb-4 h-8 w-8 text-blue-600 ${reduceMotion ? '' : 'animate-spin'}`}
                            aria-hidden="true"
                        />

                        <p className="text-center text-base font-semibold text-gray-900">{message}</p>
                        <p className="mt-1 text-center text-xs font-medium text-blue-700">
                            <BrandText>i-Link College of Science and Technology</BrandText>
                        </p>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
