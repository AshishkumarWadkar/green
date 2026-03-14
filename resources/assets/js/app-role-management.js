/**
 * Page Role Management
 */

'use strict';

// Datatable (jquery)
$(function () {
  // Variable declaration for table
  var dt_roles_table = $('.datatables-roles'),
    offCanvasForm = $('#offcanvasAddRole');

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Roles datatable
  if (dt_roles_table.length) {
    var dt_roles = dt_roles_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'user-management/roles/data'
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'name' },
        { data: 'permissions_count' },
        { data: 'users_count' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          searchable: false,
          orderable: false,
          targets: 1,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        {
          // Role name
          targets: 2,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            return full.name || '';
          }
        },
        {
          // Permissions count
          targets: 3,
          className: 'text-center',
          render: function (data, type, full, meta) {
            return full.permissions_count || '';
          }
        },
        {
          // Users count
          targets: 4,
          className: 'text-center',
          render: function (data, type, full, meta) {
            return full.users_count || '';
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return full.action || '';
          }
        }
      ],
      order: [[2, 'asc']],
      dom:
        '<"row"' +
        '<"col-md-2"<"ms-n2"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"fB>>' +
        '>t' +
        '<"row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [7, 10, 20, 50, 70, 100],
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search Role',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="ti ti-chevron-right ti-sm"></i>',
          previous: '<i class="ti ti-chevron-left ti-sm"></i>'
        }
      },
      // Buttons with Dropdown
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-secondary dropdown-toggle mx-4 waves-effect waves-light',
          text: '<i class="ti ti-upload me-2 ti-xs"></i>Export',
          buttons: [
            {
              extend: 'print',
              title: 'Roles',
              text: '<i class="ti ti-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4]
              }
            },
            {
              extend: 'csv',
              title: 'Roles',
              text: '<i class="ti ti-file-text me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4]
              }
            },
            {
              extend: 'excel',
              title: 'Roles',
              text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4]
              }
            },
            {
              extend: 'pdf',
              title: 'Roles',
              text: '<i class="ti ti-file-code-2 me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4]
              }
            },
            {
              extend: 'copy',
              title: 'Roles',
              text: '<i class="ti ti-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4]
              }
            }
          ]
        },
        {
          text: '<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Add New Role</span>',
          className: 'add-new btn btn-primary waves-effect waves-light',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasAddRole'
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== ''
                ? '<tr data-dt-row="' +
                    col.rowIndex +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      }
    });
  }

  // Delete Record
  $(document).on('click', '.delete-record', function () {
    var role_id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        // delete the data
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}user-management/roles/${role_id}`,
          success: function (response) {
            dt_roles.draw();
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: response.message || 'The role has been deleted!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          },
          error: function (error) {
            console.log(error);
            var errorMsg = 'Something went wrong!';
            if (error.responseJSON && error.responseJSON.message) {
              errorMsg = error.responseJSON.message;
            }
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: errorMsg,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The role is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // edit record
  $(document).on('click', '.edit-record', function () {
    var role_id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // changing the title of offcanvas
    $('#offcanvasAddRoleLabel').html('Edit Role');

    // get data
    $.get(`${baseUrl}user-management/roles/${role_id}/edit`, function (data) {
      $('#role_id').val(data.id);
      $('#role_name').val(data.name);
      
      // Uncheck all permissions first
      $('.permission-checkbox').prop('checked', false);
      
      // Check permissions that belong to this role
      if (data.permission_ids && data.permission_ids.length > 0) {
        data.permission_ids.forEach(function (permissionId) {
          $('#permission_' + permissionId).prop('checked', true);
        });
      }
    });
  });

  // changing the title
  $('.add-new').on('click', function () {
    $('#role_id').val(''); //reseting input field
    $('#offcanvasAddRoleLabel').html('Add Role');
    $('.permission-checkbox').prop('checked', false);
  });

  // Select All Permissions
  $('#selectAllPermissions').on('click', function () {
    $('.permission-checkbox').prop('checked', true);
  });

  // Deselect All Permissions
  $('#deselectAllPermissions').on('click', function () {
    $('.permission-checkbox').prop('checked', false);
  });

  // Filter form control to default size
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

  // validating form and updating role's data
  const addRoleForm = document.getElementById('addRoleForm');

  // role form validation
  const fv = FormValidation.formValidation(addRoleForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter role name'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: function (field, ele) {
          return '.mb-6';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    // adding or updating role when form successfully validate
    var role_id = $('#role_id').val();
    var url = role_id ? `${baseUrl}user-management/roles/${role_id}` : `${baseUrl}user-management/roles`;
    var method = role_id ? 'PUT' : 'POST';
    var status = role_id ? 'updated' : 'created';

    // Get selected permissions
    var permissions = [];
    $('.permission-checkbox:checked').each(function () {
      permissions.push($(this).val());
    });

    // Prepare form data
    var formData = {
      name: $('#role_name').val(),
      permissions: permissions
    };

    // Add _method field for PUT requests
    if (method === 'PUT') {
      formData._method = 'PUT';
    }

    $.ajax({
      data: formData,
      url: url,
      type: 'POST',
      success: function (response) {
        dt_roles.draw();
        offCanvasForm.offcanvas('hide');

        // sweetalert
        Swal.fire({
          icon: 'success',
          title: `Successfully ${status}!`,
          text: response.message || `Role ${status} successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      },
      error: function (err) {
        var errorMsg = 'Something went wrong!';
        if (err.responseJSON) {
          if (err.responseJSON.message) {
            errorMsg = err.responseJSON.message;
          } else if (err.responseJSON.errors) {
            var errors = err.responseJSON.errors;
            errorMsg = Object.values(errors).flat().join('<br>');
          }
        }
        Swal.fire({
          title: 'Error!',
          html: errorMsg,
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // clearing form data when offcanvas hidden
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    $('#role_id').val('');
    $('#role_name').val('');
    $('.permission-checkbox').prop('checked', false);
  });
});
