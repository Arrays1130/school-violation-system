import React from "react";

export default function Skeleton({ className, ...props }) {
    return (
        <div
            className={`animate-pulse rounded-md bg-slate-200/60 dark:bg-slate-800 ${className}`}
            {...props}
        />
    );
}

export function BentoSkeleton() {
    return (
        <div className="space-y-8 animate-in fade-in duration-500">
            {/* Stats Grid Skeleton */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Skeleton className="md:col-span-2 h-48 rounded-[2.5rem]" />
                <Skeleton className="h-48 rounded-[2.5rem]" />
                <Skeleton className="h-48 rounded-[2.5rem]" />
            </div>

            {/* Charts Grid Skeleton */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <Skeleton className="lg:col-span-2 h-[400px] rounded-[3rem]" />
                <Skeleton className="h-[400px] rounded-[3rem]" />
                <Skeleton className="lg:col-span-3 h-[300px] rounded-[3rem]" />
            </div>
        </div>
    );
}
