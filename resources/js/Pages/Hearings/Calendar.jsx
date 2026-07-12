import React, { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Breadcrumbs from '@/Components/Breadcrumbs';
import PageMotion, { MotionItem } from '@/Components/PageMotion';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import { Head, Link, router } from '@inertiajs/react';
import {
    Calendar, ArrowLeft, Gavel, MapPin, ChevronLeft, ChevronRight, Clock,
} from 'lucide-react';
import dayjs from 'dayjs';

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const MAX_PILLS = 3;

export default function CalendarPage({ auth, month, events = [] }) {
    const current = dayjs(`${month}-01`);
    const today = dayjs();
    const isCurrentMonth = current.isSame(today, 'month');
    const daysInMonth = current.daysInMonth();
    const startWeekday = current.startOf('month').day();

    const eventsByDay = useMemo(() => {
        const map = {};
        events.forEach((event) => {
            const day = dayjs(event.scheduled_at).date();
            if (!map[day]) map[day] = [];
            map[day].push(event);
        });
        Object.values(map).forEach((list) => {
            list.sort((a, b) => dayjs(a.scheduled_at).valueOf() - dayjs(b.scheduled_at).valueOf());
        });
        return map;
    }, [events]);

    const upcoming = useMemo(
        () => [...events]
            .filter((e) => dayjs(e.scheduled_at).isAfter(today.subtract(1, 'hour')))
            .sort((a, b) => dayjs(a.scheduled_at).valueOf() - dayjs(b.scheduled_at).valueOf()),
        [events, today],
    );

    const changeMonth = (offset) => {
        router.get(route('hearings.calendar'), {
            month: current.add(offset, 'month').format('YYYY-MM'),
        }, { preserveState: true });
    };

    const goToToday = () => {
        router.get(route('hearings.calendar'), {
            month: today.format('YYYY-MM'),
        }, { preserveState: true });
    };

    const cells = [];
    for (let i = 0; i < startWeekday; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) cells.push(d);
    while (cells.length % 7 !== 0) cells.push(null);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Hearings Calendar" />

            <PageMotion className="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
                <MotionItem>
                    <Breadcrumbs items={[
                        { label: 'Dashboard', href: route('dashboard') },
                        { label: 'Cases', href: route('cases.index') },
                        { label: 'Hearings Calendar' },
                    ]} />
                </MotionItem>

                <MotionItem className="vt-page-hero">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />
                    <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-4">
                            <Link
                                href={route('cases.index')}
                                className="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-white hover:bg-white/20 transition-colors shrink-0"
                            >
                                <ArrowLeft className="w-4 h-4" />
                            </Link>
                            <div>
                                <div className="vt-hero-chip mb-2">
                                    <Calendar className="w-3.5 h-3.5" /> Hearings
                                </div>
                                <h1 className="text-2xl font-bold text-white tracking-tight">
                                    {current.format('MMMM YYYY')}
                                </h1>
                                <p className="text-slate-400 text-sm mt-1">
                                    {events.length} hearing{events.length === 1 ? '' : 's'} this month
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            {!isCurrentMonth && (
                                <button type="button" onClick={goToToday} className="vt-hero-btn text-sm px-4">
                                    Today
                                </button>
                            )}
                            <button type="button" onClick={() => changeMonth(-1)} className="vt-hero-btn" aria-label="Previous month">
                                <ChevronLeft className="w-4 h-4" />
                            </button>
                            <button type="button" onClick={() => changeMonth(1)} className="vt-hero-btn" aria-label="Next month">
                                <ChevronRight className="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </MotionItem>

                {/* Continuous month grid — Google Calendar style */}
                <MotionItem className="vt-content-card overflow-hidden">
                    <div className="grid grid-cols-7 border-b border-slate-200 dark:border-slate-800">
                        {WEEKDAYS.map((d) => (
                            <div
                                key={d}
                                className="py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                            >
                                {d}
                            </div>
                        ))}
                    </div>

                    <div className="grid grid-cols-7">
                        {cells.map((day, idx) => {
                            const isLastCol = (idx + 1) % 7 === 0;
                            const isLastRow = idx >= cells.length - 7;

                            if (day === null) {
                                return (
                                    <div
                                        key={`pad-${idx}`}
                                        className={`min-h-[96px] sm:min-h-[112px] bg-slate-50/70 dark:bg-slate-950/40 ${
                                            !isLastCol ? 'border-r border-slate-100 dark:border-slate-800' : ''
                                        } ${!isLastRow ? 'border-b border-slate-100 dark:border-slate-800' : ''}`}
                                    />
                                );
                            }

                            const dayEvents = eventsByDay[day] || [];
                            const isToday = isCurrentMonth && day === today.date();
                            const extra = Math.max(0, dayEvents.length - MAX_PILLS);

                            return (
                                <div
                                    key={day}
                                    className={`min-h-[96px] sm:min-h-[112px] p-1.5 sm:p-2 ${
                                        !isLastCol ? 'border-r border-slate-100 dark:border-slate-800' : ''
                                    } ${!isLastRow ? 'border-b border-slate-100 dark:border-slate-800' : ''} ${
                                        isToday ? 'bg-sky-50/50 dark:bg-sky-950/20' : 'bg-white dark:bg-slate-900'
                                    }`}
                                >
                                    <div className="flex justify-end mb-1">
                                        <span
                                            className={`inline-flex items-center justify-center w-7 h-7 text-xs font-semibold rounded-full ${
                                                isToday
                                                    ? 'bg-sky-600 text-white'
                                                    : 'text-slate-600 dark:text-slate-300'
                                            }`}
                                        >
                                            {day}
                                        </span>
                                    </div>

                                    <div className="space-y-0.5">
                                        {dayEvents.slice(0, MAX_PILLS).map((event) => (
                                            <Link
                                                key={event.id}
                                                href={route('hearings.show', event.id)}
                                                title={`${event.student_name} · ${dayjs(event.scheduled_at).format('h:mm A')}`}
                                                className="block rounded px-1.5 py-0.5 text-[10px] sm:text-[11px] font-medium leading-snug truncate
                                                    bg-sky-100/90 text-sky-900 hover:bg-sky-200
                                                    dark:bg-sky-900/40 dark:text-sky-100 dark:hover:bg-sky-900/70
                                                    border-l-2 border-sky-500"
                                            >
                                                <span className="font-semibold">{dayjs(event.scheduled_at).format('h:mm A')}</span>
                                                {' '}
                                                {event.student_name?.split(',')[0]?.trim().split(/\s+/)[0] ?? 'Hearing'}
                                            </Link>
                                        ))}
                                        {extra > 0 && (
                                            <p className="text-[10px] font-semibold text-slate-400 px-1 pt-0.5">
                                                +{extra} more
                                            </p>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </MotionItem>

                {/* Upcoming list */}
                <MotionItem>
                    <div className="flex items-center justify-between mb-3">
                        <h2 className="text-sm font-bold text-slate-800 dark:text-slate-200">
                            Upcoming hearings
                        </h2>
                        <span className="text-xs text-slate-400">{upcoming.length} remaining</span>
                    </div>

                    {upcoming.length === 0 ? (
                        <div className="vt-content-card">
                            <EmptyState
                                icon={Gavel}
                                title="No upcoming hearings"
                                message={
                                    events.length > 0
                                        ? 'All hearings this month are in the past.'
                                        : `Nothing scheduled for ${current.format('MMMM YYYY')}.`
                                }
                            />
                        </div>
                    ) : (
                        <div className="vt-content-card divide-y divide-slate-100 dark:divide-slate-800 overflow-hidden">
                            {upcoming.map((event) => {
                                const when = dayjs(event.scheduled_at);
                                return (
                                    <Link
                                        key={event.id}
                                        href={route('hearings.show', event.id)}
                                        className="flex items-center gap-4 px-4 sm:px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                                    >
                                        <div className="w-12 shrink-0 text-center">
                                            <p className="text-[10px] font-bold uppercase text-slate-400 leading-none">
                                                {when.format('MMM')}
                                            </p>
                                            <p className="text-xl font-bold text-slate-900 dark:text-white leading-tight">
                                                {when.format('D')}
                                            </p>
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <p className="font-semibold text-slate-900 dark:text-white truncate">
                                                {event.student_name}
                                            </p>
                                            <p className="text-sm text-slate-500 dark:text-slate-400 truncate">
                                                {event.violation_title}
                                            </p>
                                            <div className="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                                                <span className="inline-flex items-center gap-1">
                                                    <Clock className="w-3 h-3" />
                                                    {when.format('h:mm A')}
                                                </span>
                                                {event.venue && (
                                                    <span className="inline-flex items-center gap-1 truncate">
                                                        <MapPin className="w-3 h-3 shrink-0" />
                                                        {event.venue}
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        {event.status && (
                                            <div className="hidden sm:block shrink-0">
                                                <StatusBadge status={event.status} variant="compact" />
                                            </div>
                                        )}
                                    </Link>
                                );
                            })}
                        </div>
                    )}
                </MotionItem>
            </PageMotion>
        </AuthenticatedLayout>
    );
}
