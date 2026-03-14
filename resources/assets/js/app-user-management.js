/**
 * Page User Management
 */

'use strict';

// Datatable (jquery)
$(function () {
  // Variable declaration for table
  var dt_users_table = $('.datatables-users'),
    offCanvasForm = $('#offcanvasAddUser'),
    select2 = $('.select2');

  // Initialize Select2 (dropdownParent must be offcanvas so dropdown appears inside it and selection works)
  if (select2.length) {
    select2.wrap('<div class="position-relative"></div>').select2({
      placeholder: 'Select Roles',
      dropdownParent: offCanvasForm
    });
  }

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Users datatable
  if (dt_users_table.length) {
    var dt_users = dt_users_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'user-management/users/data'
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'roles' },
        { data: 'is_active' },
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
          // User name
          targets: 2,
          render: function (data, type, full, meta) {
            return full.name || '';
          }
        },
        {
          // Email
          targets: 3,
          render: function (data, type, full, meta) {
            return full.email || '';
          }
        },
        {
          // Roles
          targets: 4,
          render: function (data, type, full, meta) {
            return full.roles || '';
          }
        },
        {
          // Status switch
          targets: 5,
          className: 'text-center',
          render: function (data, type, full, meta) {
            return full.is_active || '';
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
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"f>>' +
        '>t' +
        '<"row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [7, 10, 20, 50, 70, 100],
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search User',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="ti ti-chevron-right ti-sm"></i>',
          previous: '<i class="ti ti-chevron-left ti-sm"></i>'
        }
      }
    });
  }

  // Toggle user status
  $(document).on('change', '.toggle-status', function () {
    var user_id = $(this).data('id');
    var $checkbox = $(this);

    $.ajax({
      type: 'PATCH',
      url: `${baseUrl}user-management/users/${user_id}/toggle-status`,
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: response.message,
            timer: 1500,
            showConfirmButton: false
          });
        }
      },
      error: function (error) {
        $checkbox.prop('checked', !$checkbox.prop('checked'));
        var errorMsg = error.responseJSON?.message || 'Something went wrong!';
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: errorMsg,
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  });

  // Delete Record
  $(document).on('click', '.delete-record', function () {
    var user_id = $(this).data('id');
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
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}user-management/users/${user_id}`,
          success: function (response) {
            dt_users.draw();
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: response.message || 'The user has been deleted!',
              customClass: { confirmButton: 'btn btn-success' }
            });
          },
          error: function (error) {
            var errorMsg = error.responseJSON?.message || 'Something went wrong!';
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: errorMsg,
              customClass: { confirmButton: 'btn btn-success' }
            });
          }
        });
      }
    });
  });

  // Edit record
  $(document).on('click', '.edit-record', function () {
    var user_id = $(this).data('id');
    $('#offcanvasAddUserLabel').html('Edit User');
    $('#password_required, #password_confirmation_required').hide();
    $('#password_hint').show();

    $.get(`${baseUrl}user-management/users/${user_id}/edit`, function (data) {
      $('#user_id').val(data.id);
      $('#user_name').val(data.name);
      $('#user_email').val(data.email);
      $('#user_password, #user_password_confirmation').val('');
      $('#user_is_active').prop('checked', data.is_active);
      
      if (data.role_ids && data.role_ids.length > 0) {
        $('#user_roles').val(data.role_ids).trigger('change');
      } else {
        $('#user_roles').val(null).trigger('change');
      }
    });
  });

  // Reset form for add new
  $('.add-new').on('click', function () {
    $('#addUserForm')[0].reset();
    $('#user_id').val('');
    $('#offcanvasAddUserLabel').html('Add User');
    $('#password_required, #password_confirmation_required').show();
    $('#password_hint').hide();
    $('#user_is_active').prop('checked', true);
  });

  const addUserForm = document.getElementById('addUserForm');
  const fv = FormValidation.formValidation(addUserForm, {
    fields: {
      name: { validators: { notEmpty: { message: 'Please enter user name' } } },
      email: { validators: { notEmpty: { message: 'Please enter email' }, emailAddress: { message: 'Invalid email' } } },
      password: {
        validators: {
          callback: {
            message: 'Please enter password',
            callback: function (value) {
              const userId = $('#user_id').val();
              if (!userId && !value) return false;
              if (value && value.length < 8) return { valid: false, message: 'Min 8 chars' };
              // When password changes, revalidate password_confirmation
              fv.revalidateField('password_confirmation');
              return true;
            }
          }
        }
      },
      password_confirmation: {
        validators: {
          identical: {
            compare: function () {
              return addUserForm.querySelector('[name="password"]').value;
            },
            message: 'Must match password'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-6'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    var user_id = $('#user_id').val();
    var url = user_id ? `${baseUrl}user-management/users/${user_id}` : `${baseUrl}user-management/users`;
    var formData = $('#addUserForm').serialize();
    
    // Explicitly handle checkbox if serialization doesn't include 0
    if (!$('#user_is_active').is(':checked')) {
        formData += '&is_active=0';
    }

    $.ajax({
      data: formData,
      url: url,
      type: user_id ? 'POST' : 'POST', // Handled via _method in serialize if PUT
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function (response) {
        dt_users.draw();
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: response.message,
          customClass: { confirmButton: 'btn btn-success' }
        });
      },
      error: function (err) {
        var errorMsg = err.responseJSON?.message || 'Something went wrong!';
        if (err.responseJSON?.errors) {
            errorMsg = Object.values(err.responseJSON.errors).flat().join('<br>');
        }
        Swal.fire({
          title: 'Error!',
          html: errorMsg,
          icon: 'error',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  });

  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    $('#user_id').val('');
    $('#user_name, #user_email, #user_password, #user_password_confirmation').val('');
    $('#user_roles').val(null).trigger('change');
  });
});
