/**
 * Page Enquiries - Listing
 */

'use strict';

$(function () {
  var dt_enquiries_table = $('.datatables-enquiries'),
    offCanvasForm = $('#offcanvasEditEnquiry'),
    select2 = $('.select2'),
    flatpickr = $('.flatpickr');

  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      if ($this.hasClass('select2-hidden-accessible')) {
        $this.select2('destroy');
      }
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        width: '100%'
      });
    });
  }

  if (flatpickr.length) {
    flatpickr.flatpickr({
      dateFormat: 'Y-m-d'
    });
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  if (dt_enquiries_table.length) {
    var dt_enquiries = dt_enquiries_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'enquiries/data',
        data: function (d) {
          d.date_from = $('#filter_date_from').val();
          d.date_to = $('#filter_date_to').val();
          d.source_id = $('#filter_source').val();
          d.assigned_to = $('#filter_assigned').val();
          d.lead_type = $('#filter_lead_type').val();
          d.status = $('#filter_status').val();
          d.view = window.enquiryConfig.viewStatus;
        }
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'enquiry_date' },
        { data: 'customer_name' },
        { data: 'mobile_number' },
        { data: 'enquiry_source_id' },
        { data: 'assigned_to' },
        { data: 'created_by' },
        { data: 'lead_type' },
        { data: 'status' },
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
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full) {
            return full.action || '';
          }
        }
      ],
      order: [[2, 'desc']],
      dom:
        '<"row"' +
        '<"col-md-2"<"ms-n2"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"f>>' +
        '>t' +
        '<"row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [10, 20, 50, 100],
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search Enquiry',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="ti ti-chevron-right ti-sm"></i>',
          previous: '<i class="ti ti-chevron-left ti-sm"></i>'
        }
      },
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['customer_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var $tbody = $('<tbody/>');
            $.each(columns, function (i, col) {
              if (col.title === '') return;
              var $tr = $('<tr/>').attr({ 'data-dt-row': col.rowIndex, 'data-dt-column': col.columnIndex });
              $tr.append($('<td/>').text(col.title + ':'));
              var $td = $('<td/>');
              $td.html(col.data);
              $tr.append($td);
              $tbody.append($tr);
            });
            return $tbody.children().length ? $('<table class="table"/>').append($tbody) : false;
          }
        }
      }
    });
  }

  $('#applyFilters').on('click', function () {
    dt_enquiries.draw();
  });

  $('#collapseFilter').on('show.bs.collapse', function () {
    $('[data-bs-target="#collapseFilter"]').find('i.ti').last().removeClass('ti-chevron-down').addClass('ti-chevron-up');
  }).on('hide.bs.collapse', function () {
    $('[data-bs-target="#collapseFilter"]').find('i.ti').last().removeClass('ti-chevron-up').addClass('ti-chevron-down');
  });

  $('#resetFilters').on('click', function () {
    $('#filterForm')[0].reset();
    $('.select2').val(null).trigger('change');
    $('.flatpickr').val('');
    dt_enquiries.draw();
  });

  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

  $(document).on('click', '.edit-record', function () {
    var enquiry_id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    $('#offcanvasEditEnquiryLabel').html('Edit Enquiry');

    $.get(`${baseUrl}enquiries/${enquiry_id}/edit`, function (response) {
      if (response.success) {
        var data = response.data.enquiry;
        $('#enquiry_id').val(data.id);
        $('#enquiry_date').val(data.enquiry_date);
        $('#customer_name').val(data.customer_name);
        $('#mobile_number').val(data.mobile_number);
        $('#alternate_mobile').val(data.alternate_mobile || '');
        $('#email').val(data.email || '');
        $('#product_service').val(data.product_service || '');
        $('#initial_remark').val(data.initial_remark || '');

        $('#enquiry_source_id').empty().append('<option value="">Select source</option>');
        response.data.sources.forEach(function (source) {
          $('#enquiry_source_id').append(`<option value="${source.id}">${source.name}</option>`);
        });
        $('#enquiry_source_id').val(data.enquiry_source_id).trigger('change');

        if ($('#assigned_to').is('select')) {
          $('#assigned_to').empty().append('<option value="">Select user</option>');
          response.data.users.forEach(function (user) {
            $('#assigned_to').append(`<option value="${user.id}">${user.name}</option>`);
          });
          $('#assigned_to').val(data.assigned_to).trigger('change');
        } else {
          $('#assigned_to').val(data.assigned_to);
        }

        $('#lead_type').val(data.lead_type).trigger('change');
        $('#status').val(data.status).trigger('change');

        // Initialize Cleave for mobile numbers in edit form
        const mobileNumberEdit = document.querySelector('#mobile_number');
        if (mobileNumberEdit) {
          new Cleave(mobileNumberEdit, {
            phone: true,
            phoneRegionCode: 'IN'
          });
        }
        const alternateMobileEdit = document.querySelector('#alternate_mobile');
        if (alternateMobileEdit) {
          new Cleave(alternateMobileEdit, {
            phone: true,
            phoneRegionCode: 'IN'
          });
        }

        var offcanvasEl = document.getElementById('offcanvasEditEnquiry');
        if (offcanvasEl && typeof bootstrap !== 'undefined') {
          var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
          offcanvas.show();
        } else if (offCanvasForm.length) {
          offCanvasForm.offcanvas('show');
        }
      }
    }).fail(function (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.responseJSON?.message || 'Failed to load enquiry data',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
    });
  });

  var indianMobileRegex = /^[6-9]\d{9}$/;

  const editEnquiryForm = document.getElementById('editEnquiryForm');
  if (editEnquiryForm) {
    const fv = FormValidation.formValidation(editEnquiryForm, {
      fields: {
        enquiry_date: {
          validators: {
            notEmpty: {
              message: 'Please select enquiry date'
            }
          }
        },
        customer_name: {
          validators: {
            notEmpty: {
              message: 'Please enter customer name'
            }
          }
        },
        mobile_number: {
          validators: {
            notEmpty: {
              message: 'Please enter mobile number'
            },
            callback: {
              message: 'Please enter a valid Indian 10-digit mobile number',
              callback: function(input) {
                const phone = input.value.replace(/\D/g, '');
                return indianMobileRegex.test(phone);
              }
            }
          }
        },
        enquiry_source_id: {
          validators: {
            notEmpty: {
              message: 'Please select source'
            }
          }
        },
        lead_type: {
          validators: {
            notEmpty: {
              message: 'Please select lead type'
            }
          }
        },
        status: {
          validators: {
            notEmpty: {
              message: 'Please select status'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: function (field, ele) {
            return '.mb-3';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () {
      var enquiry_id = $('#enquiry_id').val();
      var formData = $('#editEnquiryForm').serialize() + '&_method=PUT';

      $.ajax({
        data: formData,
        url: `${baseUrl}enquiries/${enquiry_id}`,
        type: 'POST',
        success: function (response) {
          dt_enquiries.draw();
          offCanvasForm.offcanvas('hide');
          Swal.fire({
            icon: 'success',
            title: 'Successfully updated!',
            text: 'Enquiry updated successfully.',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        },
        error: function (err) {
          var errorMsg = err.responseJSON?.message || 'Something went wrong!';
          Swal.fire({
            title: 'Error!',
            text: errorMsg,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });
    });
  }

  offCanvasForm.on('hidden.bs.offcanvas', function () {
    $('#editEnquiryForm')[0].reset();
    $('.select2').val(null).trigger('change');
  });

  $(document).on('click', '.delete-record', function () {
    var enquiry_id = $(this).data('id');
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
          url: `${baseUrl}enquiries/${enquiry_id}`,
          success: function (response) {
            dt_enquiries.draw();
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Enquiry has been deleted.',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          },
          error: function (err) {
            Swal.fire({
              title: 'Error!',
              text: 'Something went wrong!',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });
      }
    });
  });

  $(document).on('click', '.update-status', function () {
    var enquiry_id = $(this).data('id');
    var status = $(this).data('status');
    var title = status === 'Accepted' ? 'Accept Enquiry?' : 'Cancel Enquiry?';
    var text = status === 'Accepted' ? "This will accept the enquiry." : "This will cancel the enquiry.";
    var confirmBtnText = status === 'Accepted' ? 'Yes, Accept!' : 'Yes, Cancel!';

    Swal.fire({
      title: title,
      text: text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: confirmBtnText,
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $.ajax({
          type: 'PATCH',
          url: `${baseUrl}enquiries/${enquiry_id}/status`,
          data: { status: status },
          success: function (response) {
            dt_enquiries.draw();
            Swal.fire({
              icon: 'success',
              title: 'Status Updated!',
              text: 'Enquiry status has been updated to ' + status,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          },
          error: function (err) {
            Swal.fire({
              title: 'Error!',
              text: err.responseJSON?.message || 'Something went wrong!',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });
      }
    });
  });
});
