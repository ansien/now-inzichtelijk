import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'
import { grid } from './grid';
import $ from 'jquery';

import '../css/app.scss';

require('bootstrap');

library.add(faSpinner);

grid.render(document.getElementById('grid'));

dom.watch();

$(document).on('click', '.nowi-modal-toggle', function () {
    const companyId = $(this).data('company-id');
    if (!companyId) return;

    $('.nowi-modal__content').load(`/company/${companyId}/detail-modal`, function() {
        $('.nowi-modal').modal('show');
    });
})
