import $ from 'jquery';
import { Grid } from 'gridjs';
import { debounce, moneyFormatter, toggleGridTotals } from '../utils';

const SECOND_BATCH_API_URL = '/api/v1/batch-entry?batch=2';

const handleApiData = (data) => {
    $('.second-batch-grid-total').text(moneyFormatter.format(data.data['totalAmount']));
    $('.second-batch-grid-average').text(moneyFormatter.format(data.data['totalAmount'] / data.data['totalResults']));

    toggleGridTotals(true);

    return data.data['result'].map((e) => [e['companyName'], e['placeName'], e['amount']]);
}

export const secondBatchGrid = new Grid({
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
            url: (prev, page) => `${prev}&page=${page + 1}`
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

                return `${prev}&order=${orderStrings.join(',')}`;
            }
        }
    },
    server: {
        url: SECOND_BATCH_API_URL,
        then: handleApiData,
        total: (data) => data.data['totalResults']
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
            'results': 'toekenningen'
        },
        loading: 'Laden...',
        noRecordsFound: 'Geen toekenningen gevonden',
        error: 'Er is een fout opgetreden, probeer het opnieuw.',
    }
});

$(document).on('input', '.second-batch-tab .search-company, .second-batch-tab .search-place', debounce(() => {
    let searchStrings = [];
    const searchCompany = $('.second-batch-tab .search-company').val();
    const searchPlace = $('.second-batch-tab .search-place').val();

    if (!!searchCompany.trim()) searchStrings.push(`companyName:${searchCompany}`);
    if (!!searchPlace.trim()) searchStrings.push(`placeName:${searchPlace}`);

    if (searchStrings.length > 0) {
        secondBatchGrid.updateConfig({
            server: {
                url: `${SECOND_BATCH_API_URL}&search=${searchStrings.join(',')}`,
                then: handleApiData,
                total: (data) => data.data['totalResults']
            }
        });
    } else {
        secondBatchGrid.updateConfig({
            server: {
                url: SECOND_BATCH_API_URL,
                then: handleApiData,
                total: (data) => data.data['totalResults']
            }
        });
    }

    toggleGridTotals(false);

    secondBatchGrid.forceRender();
}, 250));