const http = require('https');

function fetch(url, options = {}) {
    return new Promise((resolve, reject) => {
        const req = http.request(url, options, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve({ status: res.statusCode, data }));
        });
        req.on('error', reject);
        if (options.method === 'POST') req.write('');
        req.end();
    });
}

(async () => {
    console.log("Testing GET /api/config...");
    const res1 = await fetch('https://smart-rule-based-hostel-allocation.vercel.app/api/config');
    console.log("Status:", res1.status, "\nBody:", res1.data);

    console.log("\nTesting POST /api/allocation/run...");
    const res2 = await fetch('https://smart-rule-based-hostel-allocation.vercel.app/api/allocation/run', { method: 'POST' });
    console.log("Status:", res2.status, "\nBody:", res2.data);
})();
