function dataVisibleRenderer(b,a,c){if(b==0){return'<div class="status-pill grey"  title="Oculto"  data-toggle="tooltip"></div>'}else{return'<div class="status-pill green" title="Visível" data-toggle="tooltip"></div>'}}function dataStatusRenderer(b,a,c){if(b==1){return'<div class="status-pill grey"  title="Inativa" data-toggle="tooltip"></div>'}else{return'<div class="status-pill green" title="Ativa"   data-toggle="tooltip"></div>'}}function dataDatetimeRenderer(e,g,j){var i=new Date(e*1000);var h=i.getFullYear();var f=twoDigit(i.getMonth()+1);var b=twoDigit(i.getDate());var c=twoDigit(i.getHours());var d=twoDigit(i.getMinutes());return b+"/"+f+"/"+h+" "+c+":"+d}function twoDigit(a){if(a<10){return"0"+a}return a}function dataTrueFalseRenderer(b,a,c){if(b==0||b==false||b=="false"){return"não"}else{return"sim"}};