$(document).ready(function(){
    var ws = window;
    try {
        var printer = '';
        var silent  = parseInt($("#printerSettingSilent").val());
        var printerName = $("#printerSettingName").val().toString();
        var numCopy     = parseInt($("#printerSettingNumCopy").val());
        jsPrintSetup.refreshOptions();
        printer = getPrinterName(printerName);
        if(printer != ''){
            jsPrintSetup.setPrinter(printer);
        }
        jsPrintSetup.setOption('numCopies', numCopy);
        jsPrintSetup.setOption('marginTop', 0);
        jsPrintSetup.setOption('marginBottom', 0);
        jsPrintSetup.setOption('marginLeft', 0);
        jsPrintSetup.setOption('marginRight', 0);
        jsPrintSetup.setSilentPrint(silent);
        jsPrintSetup.printWindow(ws);
        ws.close();
    } catch (e) {
       ws.print();
       ws.close();
    }
});

