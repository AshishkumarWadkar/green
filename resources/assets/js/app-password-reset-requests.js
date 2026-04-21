/**
 * Password reset requests (approval dashboard)
 */

'use strict';

$(function () {
  var dt_table = $('.datatables-password-reset-requests');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  if (dt_table.length) {
    dt_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'user-management/password-reset-requests/data'
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'name' },
        { data: 'username' },
        { data: 'status' },
        { data: 'requested_at' },
        { data: 'reviewed' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function () {
            return '';
          }
        },
        {
          searchable: false,
          orderable: false,
          targets: 1,
          render: function (data, type, full) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        {
          targets: 2,
          render: function (data, type, full) {
            return full.name || '';
          }
        },
        {
          targets: 3,
          render: function (data, type, full) {
            return full.username || '';
          }
        },
        {
          targets: 4,
          render: function (data, type, full) {
            return full.status || '';
          }
        },
        {
          targets: 5,
          render: function (data, type, full) {
            return full.requested_at || '';
          }
        },
        {
          targets: 6,
          render: function (data, type, full) {
            return full.reviewed || '';
          }
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full) {
            return full.action || '';
          }
        }
      ],
      order: [[5, 'desc']],
      dom:
        '<"row"' +
        '<"col-md-2"<"ms-n2"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"f>>' +
        '>t' +
        '<"row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      displayLength: 25,
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search..'
      }
    });

    dt_table.on('click', '.btn-approve', function () {
      var id = $(this).data('id');
      $.ajax({
        url: baseUrl + 'user-management/password-reset-requests/' + id + '/approve',
        type: 'PATCH',
        success: function (res) {
          Swal.fire({
            icon: 'success',
            title: 'Done',
            text: res.message || 'Approved.',
            customClass: { confirmButton: 'btn btn-success' }
          });
          dt_table.DataTable().ajax.reload(null, false);
        },
        error: function (xhr) {
          var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to approve.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: msg,
            customClass: { confirmButton: 'btn btn-primary' }
          });
        }
      });
    });

    dt_table.on('click', '.btn-decline', function () {
      var id = $(this).data('id');
      $.ajax({
        url: baseUrl + 'user-management/password-reset-requests/' + id + '/decline',
        type: 'PATCH',
        success: function (res) {
          Swal.fire({
            icon: 'success',
            title: 'Done',
            text: res.message || 'Declined.',
            customClass: { confirmButton: 'btn btn-success' }
          });
          dt_table.DataTable().ajax.reload(null, false);
        },
        error: function (xhr) {
          var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to decline.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: msg,
            customClass: { confirmButton: 'btn btn-primary' }
          });
        }
      });
    });
  }
});
