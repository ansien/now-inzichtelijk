import $ from 'jquery';
import { Grid } from 'gridjs';
import { debounce, moneyFormatter, toggleGridTotals } from './utils';

const GRID_API_URL = '/api/v1/batch-entry';

const handleApiData = (data) => {
    $('.grid-total').text(moneyFormatter.format(data.data['totalAmount']));
    $('.grid-average').text(moneyFormatter.format(data.data['totalAmount'] / (data.data['totalResults'] !== 0 ? data.data['totalResults'] : 1)));

    toggleGridTotals(true);

    return data.data['result'].map((e) => [e['companyName'], e['placeName'], e['firstAmount'], e['secondAmount'], e['totalAmount']]);
}

export const grid = new Grid({
    columns: [
        {
            name: 'BEDRIJFSNAAM',
        },
        {
            name: 'VESTIGINGSPLAATS',
        },
        {
            name: 'BEDRAG 1.0',
            formatter: (firstAmount) => moneyFormatter.format(firstAmount),
        },
        {
            name: 'BEDRAG 2.0',
            formatter: (secondAmount) => moneyFormatter.format(secondAmount),
        },
        {
            name: 'TOTAALBEDRAG',
            formatter: (totalAmount) => moneyFormatter.format(totalAmount),
        },
    ],
    pagination: {
        limit: 15,
        server: {
            url: (prev, page) => `${prev}${prev === GRID_API_URL ? '?' : '&'}page=${page + 1}`
        }
    },
    sort: {
        multiColumn: true,
        server: {
            url: (prev, columns) => {
                let orderStrings = [];

                if (!columns.length) {
                    orderStrings.push('totalAmount:desc');
                }

                for (let col of columns) {
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    let colName = ['companyName', 'placeName', 'firstAmount', 'secondAmount', 'totalAmount'][col.index];

                    orderStrings.push(`${colName}:${dir}`);
                }

                return `${prev}${prev === GRID_API_URL ? '?' : '&'}order=${orderStrings.join(',')}`;
            }
        }
    },
    server: {
        url: GRID_API_URL,
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

$(document).on('input', '.search-company, .search-place', debounce(() => {
    let searchStrings = [];
    const searchCompany = $('.search-company').val();
    const searchPlace = $('.search-place').val();

    if (!!searchCompany.trim()) searchStrings.push(`companyName:${searchCompany}`);
    if (!!searchPlace.trim()) searchStrings.push(`placeName:${searchPlace}`);

    if (searchStrings.length > 0) {
        grid.updateConfig({
            server: {
                url: `${GRID_API_URL}?search=${searchStrings.join(',')}`,
                then: handleApiData,
                total: (data) => data.data['totalResults']
            }
        });
    } else {
        grid.updateConfig({
            server: {
                url: GRID_API_URL,
                then: handleApiData,
                total: (data) => data.data['totalResults']
            }
        });
    }

    toggleGridTotals(false);

    grid.forceRender();
}, 250));