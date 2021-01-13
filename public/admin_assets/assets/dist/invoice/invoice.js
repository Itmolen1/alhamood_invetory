$(document).on("keypress",'.quantity', function (event) {
    return isNumber(event, this)
});
$(document).on("keypress",'.price', function (event) {
    return isNumber(event, this)
});

$(document).on("keypress",'.total', function (event) {
    return isNumber(event, this)
});

$(document).on("keypress",'.cashPaid', function (event) {
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

//////////////////////// Add price ///////////
$(document).on("keyup",'.price', function () {
    var Currentrow = $(this).closest("tr");
    var QTY = Currentrow.find('.quantity').val();
    if (parseFloat(QTY) >= 0.0)
    {
        var Total = parseFloat(QTY) * parseFloat(Currentrow.find('.price').val());
        //alert(Total);
        Currentrow.find('.total').val(Total);
    }
    var vat = Currentrow.find('.VAT').val();
    RowSubTalSubtotal(vat, Currentrow);
    CountTotalVat();
});
////////// end of add price /////////////////

//////////////////////// Add quantity ///////////
$(document).on("keyup",'.quantity', function () {
    var Currentrow = $(this).closest("tr");
    var QTY = $(this).val();
    if (parseFloat(QTY) >= 0)
    {
        var Total = parseFloat(QTY) * parseFloat(Currentrow.find('.price').val());
        //alert(Total);
        Currentrow.find('.total').val(Total);
    }
    var vat = Currentrow.find('.VAT').val();

    RowSubTalSubtotal(vat, Currentrow);
    CountTotalVat();
});
///////// end of add quantity ///////////////////



//////////////////////// Add quantity ///////////
$(document).on("keyup",'.total', function () {
    var Currentrow = $(this).closest("tr");
    var tl = $(this).val();

        Currentrow.find('.total').val(tl);

    var vat = Currentrow.find('.VAT').val();

    RowSubTalSubtotal(vat, Currentrow);
    CountTotalVat();
});
///////// end of add quantity ///////////////////

/////// vat //////////////////
$(document).on("change", '.VAT', function () {
    var CurrentRow = $(this).closest("tr");
    var vat = CurrentRow.find('.VAT').val();
    RowSubTalSubtotal(vat, CurrentRow);
    CountTotalVat();

});
////////////// end of vat /////////////////

/////// vat //////////////////
$(document).on("keyup", '.VAT', function () {
    var CurrentRow = $(this).closest("tr");
    var vat = CurrentRow.find('.VAT').val();
    RowSubTalSubtotal(vat, CurrentRow);
    CountTotalVat();

});
////////////// end of vat /////////////////

///// row Sub Total ///////////////////////
function RowSubTalSubtotal(vat, CurrentRow) {

    Total = 0;
    Total = CurrentRow.find('.total').val();
    if (parseInt(vat) === 0 && typeof (vat) != "undefined" && vat !== ""){
        if (!isNaN(Total) && typeof (Total) != "undefined")
        {

            CurrentRow.find('.rowTotal').val(parseFloat(Total).toFixed(2));

            //CurrentRow.find('.rowTotal').val(Total);
            //CurrentRow.find('.rowTotal').val(parseFloat(Total).toFixed(2))
            return;
        }
    }

    if (!isNaN(Total) && Total !== "" && typeof (vat) != "undefined")
    {

        var InputVatValue = parseFloat((Total / 100) * vat);
        var ValueWTV = parseFloat(InputVatValue) + parseFloat(Total);
        // if (!isNaN(ValueWTV))
        // {
        //     CurrentRow.find('.rowTotal').val(parseFloat(ValueWTV).toFixed(2));
        // }
        CurrentRow.find('.rowTotal').val(parseFloat(ValueWTV).toFixed(2));

        CurrentRow.find('.singleRowVat').val(parseFloat(InputVatValue).toFixed(2));
    }
}
/////////////// end of row sub total ///////////////////////////


//////////// tatal vat /////////////////
function CountTotalVat() {
    var TotalVat = 0;
    var Gtotal = 0;
    var ToatWTVAT = 0;
    $('#newRow tr').each(function () {
        if ($(this).find(".rowTotal").val().trim() != ""){
            Gtotal = parseFloat(Gtotal) + parseFloat($(this).find(".rowTotal").val());
            //alert(Gtotal);
        }
        else {
            Gtotal = parseFloat(Gtotal);
        }
        if ($(this).find(".total").val().trim() != ""){
            ToatWTVAT = parseFloat(ToatWTVAT) + parseFloat($(this).find(".total").val());
            //alert(ToatWTVAT);
        }
        else {
            ToatWTVAT = parseFloat(ToatWTVAT);
        }
        TotalVat = parseFloat(Gtotal) - parseFloat(ToatWTVAT);
        // alert(TotalVat);
    });


    if (!isNaN(TotalVat)){
        $('#TotalVat').text(TotalVat.toFixed(2));
        $('.TotalVat').val(TotalVat.toFixed(2));
    }

    if (!isNaN(ToatWTVAT)){
        $('#SubTotal').text(ToatWTVAT.toFixed(2));
        $('.SubTotal').val(ToatWTVAT.toFixed(2));
    }

    $('#GTotal').text((Gtotal.toFixed(2)));
    $('.GTotal').val((Gtotal.toFixed(2)));

    $('.balance').val(Gtotal - $('.cashPaid').val());
}
//////////////// end of total vat /////////////

$(document).on("keyup",'.cashPaid',function () {
    var GTotal = $('.GTotal').val();
    var Input = parseFloat(GTotal - $('.cashPaid').val());
    //var Value = parseFloat(Input) + parseFloat(GTotal);
    var rr= $('.balance').val((Input.toFixed(2)));
});


    function totalWithCustomer(vat, rate)
    {
                                    //var Currentrow = $(this).closest("tr");
                                    var QTY = $('.quantity').val();
                                    if (parseInt(QTY) >= 0)
                                    {
                                        var Total = parseInt(QTY) * parseFloat(rate);
                                        //alert(Total);
                                        $('.total').val(Total);
                                    }

                                    Total = 0;
                                    Total = $('.total').val();
                                    //alert(Total);
                                        var InputVatValue = parseFloat((Total / 100) * vat);
                                        var ValueWTV = parseFloat(InputVatValue) + parseFloat(Total);
                                        $('.rowTotal').val(parseFloat(ValueWTV).toFixed(2));
                                        $('.singleRowVat').val(parseFloat(InputVatValue).toFixed(2));

                                    CountTotalVat();
    }
