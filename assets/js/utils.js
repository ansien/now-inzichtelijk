import $ from 'jquery';

export const debounce = (func, wait) => {
    let timeout;
    return function(...args) {
        const context = this;
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => {
            timeout = null;
            func.apply(context, args);
        }, wait);
    };
};

export const toggleGridTotals = (show) => {
    if (show) {
        $('.nowi-grid-total__loader').hide();
        $('.nowi-grid-total__content').show();
    } else {
        $('.nowi-grid-total__content').hide();
        $('.nowi-grid-total__loader').show();
    }
}

export const moneyFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
});
