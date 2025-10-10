<!DOCTYPE html>
<html>
<head>
    <title>Debug LGA API</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>LGA API Debug</h1>
    
    <div>
        <h2>Test 1: Manual States List</h2>
        <select id="test-state">
            <option value="">Select a state</option>
            <option value="1">Lagos</option>
            <option value="2">Abuja (FCT)</option>
        </select>
        
        <h3>LGAs for selected state:</h3>
        <select id="test-lga">
            <option value="">Select LGA</option>
        </select>
    </div>
    
    <div id="debug-output"></div>
    
    <script>
        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        $('#test-state').on('change', function() {
            const stateId = $(this).val();
            const lgaSelect = $('#test-lga');
            const debugOutput = $('#debug-output');
            
            debugOutput.append('<p>Testing state ID: ' + stateId + '</p>');
            
            if (!stateId) {
                lgaSelect.html('<option value="">Select LGA</option>');
                return;
            }
            
            lgaSelect.html('<option value="">Loading...</option>');
            
            // Test the API endpoint
            fetch('/api/states/' + stateId + '/lgas')
                .then(response => {
                    debugOutput.append('<p>Response status: ' + response.status + '</p>');
                    return response.text();
                })
                .then(data => {
                    debugOutput.append('<p>Raw response: <pre>' + data + '</pre></p>');
                    
                    try {
                        const jsonData = JSON.parse(data);
                        if (jsonData.success && jsonData.data) {
                            lgaSelect.html('<option value="">Select LGA</option>');
                            jsonData.data.forEach(lga => {
                                lgaSelect.append('<option value="' + lga.id + '">' + lga.name + '</option>');
                            });
                            debugOutput.append('<p style="color: green;">Success! Loaded ' + jsonData.data.length + ' LGAs</p>');
                        } else {
                            debugOutput.append('<p style="color: red;">API returned error: ' + jsonData.message + '</p>');
                        }
                    } catch (e) {
                        debugOutput.append('<p style="color: red;">JSON parse error: ' + e.message + '</p>');
                    }
                })
                .catch(error => {
                    debugOutput.append('<p style="color: red;">Fetch error: ' + error.message + '</p>');
                    lgaSelect.html('<option value="">Error loading LGAs</option>');
                });
        });
    </script>
</body>
</html>