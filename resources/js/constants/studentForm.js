export const YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
export const SECTIONS = ['A', 'B', 'C'];

export const DEPARTMENTS = [
    { value: 'College Of Business And Accounting Education', label: 'CBAE' },
    { value: 'Bachelor Of Technical Vocational Teachers Education', label: 'CTE' },
    { value: 'Bachelor Of Science In Criminology', label: 'CCJE' },
    { value: 'Bachelor Of Science In Information System', label: 'CCE' },
];

export function buildAcademicYears() {
    const currentYearNum = new Date().getFullYear();
    const years = [];
    for (let i = currentYearNum - 5; i <= currentYearNum + 5; i++) {
        years.push(`SY ${i}-${i + 1}`);
    }
    return years;
}
