var FIRST_BATCH_API_URL = '/api/v1/first-batch';

var firstBatchGridInitialized = false;

var moneyFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
});

function checkGrids() {
    if (!firstBatchGridInitialized) {
        firstBatchGrid.render(document.getElementById('first-batch-grid'));
    }
}

function toggleGridTotals(show) {
    if (show) {
        $('.grid-total-loader').hide();
        $('.grid-total-content').show();
    } else {
        $('.grid-total-content').hide();
        $('.grid-total-loader').show();
    }
}

$(function() {
    checkGrids();
});

$('.grids-wrapper a[data-toggle="tab"]').on('shown.bs.tab', function () {
    checkGrids();
})