
// This script uses the QuaggaJS library for barcode scanning
// Make sure to include the QuaggaJS library in your HTML file before this script

let scanner = null;

function initBarcodeScanner(callback) {
    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.querySelector('#scanner-container'),
            constraints: {
                width: 480,
                height: 320,
                facingMode: "environment"
            },
        },
        decoder: {
            readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "code_39_vin_reader", "codabar_reader", "upc_reader", "upc_e_reader", "i2of5_reader"],
            debug: {
                showCanvas: true,
                showPatches: true,
                showFoundPatches: true,
                showSkeleton: true,
                showLabels: true,
                showPatchLabels: true,
                showRemainingPatchLabels: true,
                boxFromPatches: {
                    showTransformed: true,
                    showTransformedBox: true,
                    showBB: true
                }
            }
        },
    }, function (err) {
        if (err) {
            console.log(err);
            return;
        }
        console.log("Quagga initialization finished. Ready to start");
        scanner = Quagga;
        scanner.start();
    });

    Quagga.onDetected(function (result) {
        var code = result.codeResult.code;
        callback(code);
        scanner.stop();
    });
}

function stopBarcodeScanner() {
    if (scanner) {
        scanner.stop();
    }
}

// Usage example:
// initBarcodeScanner(function(barcode) {
//     console.log("Detected barcode: " + barcode);
//     // Here you can send the barcode to your server or perform any other action
// });
