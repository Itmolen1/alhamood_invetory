/**
 * Created by rizwanafridi on 11/26/20.
 */


$(document).on("keypress",'.startReading', function (event) {
    return isNumber(event, this)
});
$(document).on("keypress",'.endReading', function (event) {
    return isNumber(event, this)
});

$(document).on("keypress",'.purchases', function (event) {
    return isNumber(event, this)
});

$(document).on("keypress",'.totalSale', function (event) {
    return isNumber(event, this)
});

$(document).on("keypress",'.startPad', function (event) {
    return isNumber(event, this)
});

$(document).on("keypress",'.endPad', function (event) {
    return isNumber(event, this)
});

////////////////// accept number function ////////////////
function isNumber(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (
        (charCode !== 46 || $(element).val().indexOf('.') !== -1) &&      // “.” CHECK DOT, AND ONLY ONE.
        (charCode < 48 || charCode > 57))
        return false;
    return true;
}
//////////////// end of accept number function //////////////



//////////////////////// Add quantity ///////////
$(document).on("keyup",'.startReading', function () {
    var Currentrow = $(this).closest("tr");
    var startReading = $(this).val();
    if (parseInt(startReading) >= 0)
    {
        var sum1 = parseFloat(Currentrow.find('.endReading').val()) - parseInt(startReading);
        // var sum = parseInt(sum1) - parseFloat(Currentrow.find('.purchases').val());
        //alert(Total);
        Currentrow.find('.netReading').val(sum1);
        // Currentrow.find('.sales').val(sum) ;
        Currentrow.find('.totalRow').val(sum1) ;
        var pur = Currentrow.find('.purchases').val();
        pchase(pur, Currentrow);
        CountTotal()

    }
});
///////// end of add quantity ///////////////////

//////////////////////// Add quantity ///////////
$(document).on("keyup",'.endReading', function () {
    var Currentrow = $(this).closest("tr");
    var endReading = $(this).val();
    if (parseInt(endReading) >= 0)
    {
        var sum1 = parseInt(endReading) - parseFloat(Currentrow.find('.startReading').val())
        //var sum = parseInt(sum1) - parseFloat(Currentrow.find('.purchases').val());
        //alert(Total);
        Currentrow.find('.netReading').val(sum1);
        //Currentrow.find('.sales').val(sum) ;
        Currentrow.find('.totalRow').val(sum1) ;
        var pur = Currentrow.find('.purchases').val();
        pchase(pur, Currentrow);
        CountTotal()
    }
});
///////// end of add quantity ///////////////////

//////////////////////// Add quantity ///////////
$(document).on("keyup",'.purchases', function () {
    var Currentrow = $(this).closest("tr");
    var purchases = $(this).val();
    if (parseInt(purchases) >= 0)
    {
        var sum = parseFloat(Currentrow.find('.totalRow').val()) - parseInt(purchases);
        //alert(Total);
        Currentrow.find('.sales').val(sum) ;
        CountTotal()
    }
});
///////// end of add quantity ///////////////////

//////////////////////// Add quantity ///////////
$(document).on("keyup",'.sales', function () {
    var Currentrow = $(this).closest("tr");
    var sales = $(this).val();
    if (parseInt(sales) >= 0)
    {
        var sum = parseInt(sales) - parseFloat(Currentrow.find('.purchases').val());
        //alert(Total);
        Currentrow.find('.netDifference').val(sum);
        CountTotal()
    }
});
///////// end of add quantity ///////////////////



//////// validate rows ////////
function validateRow(currentRow) {

    var isvalid = true;
    var endReading = 0, meter = 0, startReading = 0, meterReadingDate = $('#meterReadingDate').val();
    if (parseInt(meterReadingDate) === 0 || meterReadingDate === ""){
        isvalid = false;
    }

    meter = currentRow.find('.meter_id').val();
    startReading  = currentRow.find('.startReading').val();
    endReading = currentRow.find('.endReading').val();
    if (parseInt(meter) === 0 || meter === ""){
        //alert(product);
        isvalid = false;

    }
    if (parseInt(startReading) == 0 || startReading == "")
    {
        isvalid = false;
    }
    if (parseInt(endReading) == 0 || endReading == "")
    {
        isvalid = false
    }
    return isvalid;
}
////// end of validate row ///////////////////


//////////// tatal  /////////////////
function CountTotal() {
    var Totalvalue = 0;
    var Gtotal = 0;
    $('#newRow tr').each(function () {
        if ($(this).find(".sales").val().trim() !== ""){
            Gtotal = parseFloat(Gtotal) + parseFloat($(this).find(".sales").val());
        }
        else {
            Gtotal = parseFloat(Gtotal);
        }
    });

    $('.totalSale').val((Gtotal.toFixed(2)));
    var Input = parseFloat(Gtotal - $('.totalPad').val());
    $('.balance').val((Input.toFixed(2)));

}
//////////////// end of total  /////////////

//////////// tatal  /////////////////
function pchase(pur, Currentrow) {
    var sum = parseFloat(Currentrow.find('.totalRow').val()) - parseInt(pur);
    //alert(Total);
    Currentrow.find('.sales').val(sum) ;
    CountTotal()
}
//////////////// end of total  /////////////

//////////////balance ////////////////////
// $(document).on("keyup",'.tatalPad',function () {
//     var GTotal = $('.totalSale').val();
//     var Input = parseFloat(GTotal - $('.tatalPad').val());
//     var rr= $('.balance').val((Input.toFixed(2)));
// });
////////////// balance end ///////////////////////
