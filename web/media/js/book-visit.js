$(function () {
  $('.selectpicker').selectpicker();
  
  $('#visitPlanningModal').on('shown.bs.modal', function () {
    $('#speciality').selectpicker('val', bookVisit.speciality);
    $('#department').selectpicker('val', bookVisit.department);
    $('#booking-date').data("DateTimePicker").date(bookVisit.date)
  })
});

$('#select-speciality').on('change', function (e) {
  bookVisit.speciality = $(e.target).val();
});
$('#select-department').on('change', function (e) {
  bookVisit.department = $(e.target).val();
});
$('#set-booking-date').on("dp.change", function(e) {
  bookVisit.date = e.date;
});

var bookVisit = {
  speciality: '',
  department: '',
  date: '',
  doctor: {
    id: '',
    name: ''
  },
  cabinet: {
    id: '',
    name: ''
  }
};

var selectStak = [];
var curentItem;
var currentCol;
var currentType;

document.addEventListener("mousedown", function(e){
  startTimeSelect(e);
});
document.addEventListener("mouseup", function(e){
  endTimeSelect(e);
});

startTimeSelect = function(e){
  curentItem = e.target.closest('.vp-time');
  if(!curentItem || $(curentItem).hasClass('disable')) return false;
  
  currentCol = e.target.closest('.vp-column');
  field = $(currentCol).data('field');
  currentType = field;
  
  if($(currentCol).data('value') !== bookVisit[field]['name'] || selectStak.length > 0){
    selectStak = [];
    $('[data-field="'+field+'"] .vp-time').removeClass('active');
    $('#'+field+'StartTime').val('');
    $('#'+field+'EndTime').val('');
  }
  
  bookVisit[field]['id'] = $(currentCol).data('id');
  bookVisit[field]['name'] = $(currentCol).data('value');
  handleTime(curentItem);
  $(currentCol).bind('mousemove', function (e) {
    target = e.target.closest('.vp-time');
    if (target !== curentItem) {
      curentItem = e.target.closest('.vp-time');
      handleTime(curentItem);
    }
  });
  $(currentCol).mouseleave(function () {
    $(currentCol).unbind('mousemove');
  })
};

endTimeSelect = function(e) {
  if(currentCol) {
    $(currentCol).unbind('mousemove');
    countTime(currentType, selectStak);
  }
};

function handleTime(item){
  if(!item) return false;
  index = jQuery.inArray(item, selectStak);
  if(index < 0){
    item.classList.add('active');
    selectStak.push(item);
  }
  // else{
  //   item.classList.remove('active');
  //   selectStak.splice(index, 1);
  // }
}

function countTime(type, items) {
  count = items.length;
  if(count == 0){
    $('#'+field+'StartTime').val('');
    $('#'+field+'EndTime').val('');
    return false;
  }
  startTime = moment($(items[0]).data('time'), 'HH:mm');
  endTime = startTime;
  for(i = 0; i < count; i++){
    time = moment($(items[i]).data('time'), 'HH:mm');
    if(time.isBefore(startTime)){
      startTime = time;
    }else if(time.isAfter(endTime)){
      endTime = time;
    }
  }
  $('#'+type+'Id').val(bookVisit[type]['id']);
  $('#'+type+'Name').val(bookVisit[type]['name']);
  $('#'+type+'StartTime').val(startTime.format('HH:mm'));
  $('#'+type+'EndTime').val(endTime.add(30, 'm').format('HH:mm'));  // .add(30, 'm'); ?
}

function clearReservation() {
  bookVisit = {
    speciality: '',
    department: '',
    date: '',
    doctor: {
      id: '',
      name: ''
    },
    cabinet: {
      id: '',
      name: ''
    }
  };
  selectStak = [];
  curentItem = null;
  currentCol = null;
  currentType = null;
  $('#doctorName').val('');
  $('#doctorStartTime').val('');
  $('#doctorEndTime').val('');
  $('#cabinrtName').val('');
  $('#cabinetStartTime').val('');
  $('#cabinetEndTime').val('');
  $('.vp-time').removeClass('active');
}


