import $ from 'jquery';

export const debounce = (func, delay) => {
    let inDebounce;
    return () => {
        const context = this;
        const args = arguments;
        clearTimeout(inDebounce);
        inDebounce = setTimeout(() => func.apply(context, args), delay);
    }
}

export const toggleGridTotals = (show) => {
    if (show) {
        $('.grid-total-loader').hide();
        $('.grid-total-content').show();
    } else {
        $('.grid-total-content').hide();
        $('.grid-total-loader').show();
    }
}

export const moneyFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
});