var firstBatchGrid = new gridjs.Grid({
    columns: [
        'BEDRIJFSNAAM',
        'VESTIGINGSPLAATS',
        {
            name: 'BEDRAG',
            formatter: (amount) => moneyFormatter.format(amount)
        },
    ],
    pagination: {
        limit: 15,
        server: {
            url: (prev, page) => {
                return `${prev}${prev === FIRST_BATCH_API_URL ? '?' : '&'}page=${page + 1}`;
            }
        }
    },
    sort: {
        multiColumn: true,
        server: {
            url: (prev, columns) => {
                let orderStrings = [];

                if (!columns.length) {
                    orderStrings.push('amount:desc');
                }

                for (let col of columns) {
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    let colName = ['companyName', 'placeName', 'amount'][col.index];

                    orderStrings.push(`${colName}:${dir}`);
                }

                return `${prev}${prev === FIRST_BATCH_API_URL ? '?' : '&'}order=${orderStrings.join(',')}`;
            }
        }
    },
    server: {
        url: FIRST_BATCH_API_URL,
        then: handleGridData,
        total: data => data.data['totalResults']
    },
    language: {
        'search': {
            'placeholder': '🔍 Zoeken...'
        },
        'pagination': {
            'previous': 'Vorige',
            'next': 'Volgende️',
            'showing': ' ',
            'of': 'van de',
            'to': 'tot',
            'results': () => 'toekenningen'
        },
        loading: 'Laden...',
        noRecordsFound: 'Geen toekenningen gevonden',
        error: 'Er is een fout opgetreden, probeer het opnieuw.',
    }
});

firstBatchGrid.on('beforeLoad', () => {
    toggleGridTotals(false);
});

function handleGridData(data) {
    $('.first-batch-grid-total').text(moneyFormatter.format(data.data['totalAmount']));
    $('.first-batch-grid-average').text(moneyFormatter.format(data.data['totalAmount'] / data.data['totalResults']));
    toggleGridTotals(true);

    return data.data['result'].map(l => [l['companyName'], l['placeName'], l['amount']])
}

$(document).on('input', '#now-1 .search-company, #now-1 .search-place', debounce(function() {
    var searchStrings = [];
    var searchCompany = $('#now-1 .search-company').val();
    var searchPlace = $('#now-1 .search-place').val();

    if (!!searchCompany.trim()) searchStrings.push(`companyName:${searchCompany}`);
    if (!!searchPlace.trim()) searchStrings.push(`placeName:${searchPlace}`);

    if (searchStrings.length > 0) {
        firstBatchGrid.updateConfig({
            server: {
                url: `${FIRST_BATCH_API_URL}?search=${searchStrings.join(',')}`,
                then: handleGridData,
                total: data => data.data['totalResults']
            }
        });
    } else {
        firstBatchGrid.updateConfig({
            server: {
                url: FIRST_BATCH_API_URL,
                then: handleGridData,
                total: data => data.data['totalResults']
            }
        });
    }

    firstBatchGrid.forceRender();
}, 250));