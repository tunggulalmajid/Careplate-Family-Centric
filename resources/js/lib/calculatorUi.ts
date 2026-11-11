/** A BMI is always shown with one decimal ("22,0", not "22"), as the calculator designs do. */
export const bmiNumber = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1,
});
