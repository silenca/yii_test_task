
var bookVisit = {
  doctor: {
    date: '',
    timeStart: '',
    timeEnd: '',
    name: ''
  },
  cabinet: {
    date: '',
    timeStart: '',
    timeEnd: '',
    name: ''
  }
};

var selectStak = [];
var curentItem;
var currentType;

$(function () {
});

$('.vp-column').bind('mousedown', function(e){
  currentCol = e.target.closest('.vp-column');
  field = $(currentCol).data('field');
  currentType = field;
  
  if($(currentCol).data('value') !== bookVisit[field]['name'] || selectStak.length > 0){
    selectStak = [];
    $('[data-field="'+field+'"] .vp-time').removeClass('active');
    $('#'+field+'StartTime').val('');
    $('#'+field+'EndTime').val('');
  }
  
  bookVisit[field]['name'] = $(currentCol).data('value');
  bookVisit[field]['date'] = $(currentCol).data('date');
  curentItem = e.target.closest('.vp-time');
  handleTime(curentItem);
  $('.vp-column').bind('mousemove', function (e) {
    target = e.target.closest('.vp-time');
    if (target !== curentItem) {
      curentItem = e.target.closest('.vp-time');
      handleTime(curentItem);
    }
  })
});

$('.vp-column').bind('mouseup', function(e){
  $(".vp-column").unbind('mousemove');
  countTime(currentType, selectStak);
});
$('.vp-column').bind('mouseleave', function(e){
  $(".vp-column").unbind('mousemove');
});

function handleTime(item){
  if(!item) return false;
  index = jQuery.inArray(item, selectStak);
  if(index < 0){
    item.classList.add('active');
    selectStak.push(item);
  }else{
    item.classList.remove('active');
    selectStak.splice(index, 1);
  }
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
  $('#'+type+'StartTime').val(startTime.format('HH:mm'));
  $('#'+type+'EndTime').val(endTime.add(29, 'm').format('HH:mm'));  // .add(30, 'm'); ?
  $('#'+type+'Name').val(bookVisit[type]['name']);
  
}

function clearReservation() {
  bookVisit = {
    doctor: {
      date: '',
      timeStart: '',
      timeEnd: '',
      name: ''
    },
    cabinet: {
      date: '',
      timeStart: '',
      timeEnd: '',
      name: ''
    }
  };
  selectStak = [];
  curentItem = null;
  currentType = null;
  $('#doctorName').val('');
  $('#doctorStartTime').val('');
  $('#doctorEndTime').val('');
  $('#cabinrtName').val('');
  $('#cabinetStartTime').val('');
  $('#cabinetEndTime').val('');
  $('.vp-time').removeClass('active');
}