import $ from 'jquery';
import { Grid, html } from 'gridjs';
import { debounce, moneyFormatter, toggleGridTotals } from './utils';

const GRID_API_URL = '/api/v1/data';

const handleApiData = (data) => {
    $('.grid-total').text(moneyFormatter.format(data.data['totalAmount']));
    $('.grid-average').text(moneyFormatter.format(data.data['totalAmount'] / (data.data['totalResults'] !== 0 ? data.data['totalResults'] : 1)));

    toggleGridTotals(true);

    return data.data['result'].map((e) => [
        e['companyId'],
        e['companyName'],
        e['placeName'],
        e['depositedAmount'],
        e['updatedAmount'],
    ]);
}

export const grid = new Grid({
    columns: [
        {
            name: 'companyId',
            hidden: true,
        },
        {
            name: 'BEDRIJFSNAAM',
        },
        {
            name: 'VESTIGINGSPLAATS',
        },
        {
            name: 'VOORSCHOT ONTVANGEN',
            formatter: (depositedAmount, r, d) => {
                return html(`<a href='javascript:void(0)' class='nowi-modal-toggle' data-company-id='${r._cells[0].data}'>${moneyFormatter.format(depositedAmount)}</a>`);
            }
        },
        {
            name: 'VASTGESTELD BEDRAG',
            formatter: (updatedAmount, r, d) => {
                return html(`<a href='javascript:void(0)' class='nowi-modal-toggle' data-company-id='${r._cells[0].data}'>${moneyFormatter.format(updatedAmount)}</a>`);
            }
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
                    orderStrings.push('depositedAmount:desc');
                }

                for (let col of columns) {
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    let colName = ['companyName', 'placeName', 'depositedAmount', 'updatedAmount'][col.index - 1];

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
