function printJs(printer,printPage,footerStrLeft,footerStrRight,footerStrCenter,numCopier,scale,headerRight,silent){
    jsPrintSetup.refreshOptions();
    jsPrintSetup.setPrinter(printer);
    jsPrintSetup.setOption('marginTop', 12);
    jsPrintSetup.setOption('marginBottom', 12);
    jsPrintSetup.setOption('marginLeft', 0);
    jsPrintSetup.setOption('marginRight', 0);
    // set page header
    jsPrintSetup.setOption('headerStrLeft', footerStrLeft);
    jsPrintSetup.setOption('headerStrCenter', '');
    jsPrintSetup.setOption('headerStrRight', headerRight);
    jsPrintSetup.setOption('footerStrLeft', footerStrLeft);
    jsPrintSetup.setOption('footerStrCenter', footerStrCenter);
    jsPrintSetup.setOption('footerStrRight', footerStrRight);
    
    jsPrintSetup.setSilentPrint(silent);
    jsPrintSetup.setOption('scaling', scale);
    jsPrintSetup.setOption('printBGImages', 1);
    jsPrintSetup.setOption('printBGColors', 1);
    jsPrintSetup.setOption('numCopies', numCopier);
    jsPrintSetup.printWindow(printPage);
    jsPrintSetup.setSilentPrint(0);
}

function getPrinterBig(location){
    var printer = "";
    var silent  = 1;
    if(location == 1){
        silent  = 0;
        printer = jsPrintSetup.getPrinter()+"|**|"+silent;
    }else if(location == 2){
        silent  = 0;
        printer = jsPrintSetup.getPrinter()+"|**|"+silent;
    }else if(location == 3){
        if(jsPrintSetup.getPrinter() == "Big01" || jsPrintSetup.getPrinter() == "007" || jsPrintSetup.getPrinter() == "\\\\192.168.5.151\\007"){
            if(jsPrintSetup.getPrinter() == "Big01" || jsPrintSetup.getPrinter() == "007"){
                printer = "Big01"+"|**|"+silent;
            }else{
                printer = "Big"+"|**|"+silent;
            }
        }else{
            silent  = 0;
            printer = jsPrintSetup.getPrinter()+"|**|"+silent;
        }
    }
    return printer;
}

function getPrinterSm(location){
    var printer = "";
    var silent  = 1;
    if(location == 1){
        silent  = 0;
        printer = jsPrintSetup.getPrinter()+"|**|"+silent;
    }else if(location == 2){
        silent  = 0;
        printer = jsPrintSetup.getPrinter()+"|**|"+silent;
    }else if(location == 3){
        if(jsPrintSetup.getPrinter() == "Big01" || jsPrintSetup.getPrinter() == "007" || jsPrintSetup.getPrinter() == "\\\\192.168.5.151\\007"){
            if(jsPrintSetup.getPrinter() == "Big01" || jsPrintSetup.getPrinter() == "007"){
                printer = "007"+"|**|"+silent;
            }else{
                printer = "\\\\192.168.5.151\\007"+"|**|"+silent;
            }
        }else{
            silent  = 0;
            printer = jsPrintSetup.getPrinter()+"|**|"+silent;
        }
    }
    return printer;
}
