'use strict';

$(function () {
  var dt_followups_table = $('.datatables-followups'),
    dt_completed_followups_table = $('.datatables-completed-followups'),
    select2 = $('.select2'),
    flatpickr = $('.flatpickr'),
    completeFollowupModal = $('#completeFollowupModal'),
    completeFollowupForm = $('#completeFollowupForm');

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

  $('#collapseFollowupFilter')
    .on('show.bs.collapse', function () {
      $('[data-bs-target="#collapseFollowupFilter"]')
        .find('i.ti')
        .last()
        .removeClass('ti-chevron-down')
        .addClass('ti-chevron-up');
    })
    .on('hide.bs.collapse', function () {
      $('[data-bs-target="#collapseFollowupFilter"]')
        .find('i.ti')
        .last()
        .removeClass('ti-chevron-up')
        .addClass('ti-chevron-down');
    });

  const followupScope = $('[data-followup-scope]').data('followupScope') || 'today';

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const followupDatePicker = $('#followup_next_date').flatpickr({
    dateFormat: 'Y-m-d',
    minDate: 'today'
  });

  var dt_followups = null;
  var dt_completed_followups = null;
  if (dt_followups_table.length) {
    dt_followups = dt_followups_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'enquiries/followups/data',
        data: function (d) {
          d.scope = followupScope;
          d.date_from = $('#filter_followup_date_from').val();
          d.date_to = $('#filter_followup_date_to').val();
          d.assigned_to = $('#filter_followup_assigned').val();
        }
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'next_follow_up_date' },
        { data: 'enquiry_date' },
        { data: 'customer_name' },
        { data: 'mobile_number' },
        { data: 'assigned_to' },
        { data: 'follow_up_remark' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          orderable: false,
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
      order: [[2, 'asc']]
    });
  }

  if (dt_completed_followups_table.length) {
    dt_completed_followups = dt_completed_followups_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'enquiries/followups/completed-data',
        data: function (d) {
          d.date_from = $('#filter_followup_date_from').val();
          d.date_to = $('#filter_followup_date_to').val();
          d.assigned_to = $('#filter_followup_assigned').val();
        }
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'follow_up_date' },
        { data: 'customer_name' },
        { data: 'mobile_number' },
        { data: 'assigned_to' },
        { data: 'new_status' },
        { data: 'next_follow_up_date' },
        { data: 'remark' },
        { data: 'updated_by' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          orderable: false,
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
          searchable: false,
          orderable: false,
          render: function (data, type, full) {
            return full.action || '';
          }
        }
      ],
      order: [[2, 'desc']]
    });
  }

  $('#followupFilterForm').on('change', 'input, select', function () {
    if (dt_followups) {
      dt_followups.draw();
    }
    if (dt_completed_followups) {
      dt_completed_followups.draw();
    }
  });

  $('#resetFollowupFilters').on('click', function () {
    $('#followupFilterForm')[0].reset();
    $('.select2').val(null).trigger('change');
    $('.flatpickr').val('');
    if (dt_followups) {
      dt_followups.draw();
    }
    if (dt_completed_followups) {
      dt_completed_followups.draw();
    }
  });

  function toggleNextDateRequirement() {
    const isPending = $('#followup_status').val() === 'Pending';
    $('#nextDateRequired').toggleClass('d-none', !isPending);
    if (!isPending) {
      followupDatePicker.clear();
    }
  }

  $(document).on('click', '.complete-followup', function () {
    const enquiryId = $(this).data('id');
    const customerName = $(this).data('customer');
    $('#followup_record_id').val('');
    $('#followup_enquiry_id').val(enquiryId);
    $('#followup_customer_name').val(customerName || '');
    $('#followup_status').val('Pending');
    completeFollowupForm.data('enquiryStatus', '');
    $('#followup_status option[value="Cancelled"]').prop('disabled', false);
    $('#followup_remark_input').val('');
    followupDatePicker.clear();
    toggleNextDateRequirement();
    completeFollowupModal.modal('show');
  });

  $(document).on('click', '.edit-followup', function () {
    const followupId = $(this).data('id');

    $.get(`${baseUrl}enquiries/followups/${followupId}/edit`, function (response) {
      if (!response.success) {
        return;
      }

      const data = response.data;
      $('#followup_record_id').val(data.id);
      $('#followup_enquiry_id').val(data.enquiry_id);
      $('#followup_customer_name').val(data.customer_name || '');
      completeFollowupForm.data('enquiryStatus', data.enquiry_status || '');
      $('#followup_status').val(data.status || 'Pending');
      refreshFollowupCancelOption();
      $('#followup_remark_input').val(data.remark || '');
      if (data.next_follow_up_date) {
        followupDatePicker.setDate(data.next_follow_up_date, true, 'Y-m-d');
      } else {
        followupDatePicker.clear();
      }
      toggleNextDateRequirement();
      completeFollowupModal.modal('show');
    }).fail(function (err) {
      let errorMsg = err.responseJSON?.message || 'Failed to load follow-up.';
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errorMsg
      });
    });
  });

  function refreshFollowupCancelOption() {
    const enquiryStatus = completeFollowupForm.data('enquiryStatus') || '';
    const disabled =
      enquiryStatus === 'Accepted' && $('#followup_status').val() === 'Accepted';
    $('#followup_status option[value="Cancelled"]').prop('disabled', disabled);
  }

  $('#followup_status').on('change', function () {
    toggleNextDateRequirement();
    refreshFollowupCancelOption();
  });

  completeFollowupForm.on('submit', function (e) {
    e.preventDefault();
    const enquiryId = $('#followup_enquiry_id').val();
    const followupRecordId = $('#followup_record_id').val();
    const status = $('#followup_status').val();
    const remark = $('#followup_remark_input').val().trim();
    const nextDate = $('#followup_next_date').val();

    if (!remark) {
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Remark is required.'
      });
      return;
    }

    if (status === 'Pending' && !nextDate) {
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Next follow-up date is required when status is Pending.'
      });
      return;
    }

    function submitFollowUpSave() {
      $.ajax({
        type: followupRecordId ? 'PUT' : 'PATCH',
        url: followupRecordId ? `${baseUrl}enquiries/followups/${followupRecordId}` : `${baseUrl}enquiries/${enquiryId}/followup-complete`,
        data: {
          status: status,
          remark: remark,
          next_follow_up_date: nextDate
        },
        success: function (response) {
          completeFollowupModal.modal('hide');
          if (dt_followups) {
            dt_followups.draw();
          }
          if (dt_completed_followups) {
            dt_completed_followups.draw();
          }
          Swal.fire({
            icon: 'success',
            title: 'Updated',
            text: response.message || 'Follow-up saved successfully.'
          });
        },
        error: function (err) {
          let errorMsg = err.responseJSON?.message || 'Something went wrong!';
          if (err.responseJSON?.errors) {
            errorMsg = Object.values(err.responseJSON.errors).flat().join('\n');
          }
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMsg
          });
        }
      });
    }

    if (status === 'Accepted' || status === 'Cancelled') {
      const isAccept = status === 'Accepted';
      Swal.fire({
        title: isAccept ? 'Confirm this enquiry?' : 'Cancel this enquiry?',
        text: isAccept
          ? 'The enquiry will be marked as accepted.'
          : 'The enquiry will be marked as cancelled.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'No, go back',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.isConfirmed || result.value) {
          submitFollowUpSave();
        }
      });
    } else {
      submitFollowUpSave();
    }
  });
});
