/** iLink / VioTrack brand tokens — slate + indigo */
export const BRAND = {
    primary: '#4f46e5',      // indigo-600
    primaryDark: '#4338ca',  // indigo-700
    primaryDeep: '#3730a3',  // indigo-800
    accent: '#6366f1',       // indigo-500
    accentLight: '#818cf8',  // indigo-400
};

export const CHART = {
    line: BRAND.primary,
    lineFill: 'rgba(79, 70, 229, 0.18)',
    barFrom: BRAND.primaryDark,
    barTo: BRAND.accentLight,
    barHover: BRAND.primaryDeep,
    tooltipBg: 'rgba(30, 27, 75, 0.95)',
};

export const SEVERITY_COLORS = {
    Minor: '#818cf8',
    Major: '#4f46e5',
    Critical: '#f43f5e',
};

export const chartBaseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    layout: { padding: { top: 15, right: 15, left: 5, bottom: 5 } },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: CHART.tooltipBg,
            titleColor: '#f8fafc',
            bodyColor: '#cbd5e1',
            titleFont: { size: 13, weight: '700', family: "'Inter', sans-serif" },
            bodyFont: { size: 13, weight: '500', family: "'Inter', sans-serif" },
            padding: 12,
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            cornerRadius: 12,
            displayColors: true,
            usePointStyle: true,
            boxWidth: 8,
            boxHeight: 8,
            boxPadding: 6,
            callbacks: {
                label(context) {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
                    if (context.parsed.y !== null) {
                        label += context.parsed.y + (context.parsed.y === 1 ? ' case' : ' cases');
                    }
                    return label;
                },
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 11, weight: '600', family: "'Inter', sans-serif" }, color: '#94a3b8' },
            border: { display: false },
        },
        y: {
            grid: { color: 'rgba(148, 163, 184, 0.2)', drawBorder: false, borderDash: [5, 5] },
            ticks: { stepSize: 1, font: { size: 11, weight: '600', family: "'Inter', sans-serif" }, color: '#94a3b8' },
            beginAtZero: true,
            border: { display: false },
            grace: 1,
        },
    },
};

export const motionContainer = {
    hidden: { opacity: 0 },
    show: { opacity: 1, transition: { staggerChildren: 0.08 } },
};

export const motionItem = {
    hidden: { opacity: 0, y: 16 },
    show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 26 } },
};
