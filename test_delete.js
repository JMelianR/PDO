const http = require('http');

// First, get a real token by logging in
function makeRequest(options, body) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        console.log(`Status: ${res.statusCode}`);
        console.log('Response:', data);
        resolve({ status: res.statusCode, body: data });
      });
    });
    req.on('error', reject);
    if (body) req.write(body);
    req.end();
  });
}

async function run() {
  // Step 1: Login to get token
  console.log('--- LOGIN ---');
  const loginBody = JSON.stringify({ username: 'admin', password: 'admin123' });
  const loginRes = await makeRequest({
    hostname: 'localhost',
    port: 8888,
    path: '/api/auth',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(loginBody) }
  }, loginBody);

  let token;
  try {
    const parsed = JSON.parse(loginRes.body);
    token = parsed.token;
    console.log('Token obtained:', token ? 'YES' : 'NO');
  } catch(e) {
    console.log('Login failed:', e.message);
    return;
  }

  if (!token) {
    console.log('No token, aborting');
    return;
  }

  // Step 2: Get subjects list
  console.log('\n--- GET SUBJECTS ---');
  const getRes = await makeRequest({
    hostname: 'localhost',
    port: 8888,
    path: '/api/admin',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${token}` }
  });

  let subjectId;
  try {
    const data = JSON.parse(getRes.body);
    console.log('Subjects:', JSON.stringify(data.subjects));
    if (data.subjects && data.subjects.length > 0) {
      subjectId = data.subjects[data.subjects.length - 1].id; // last subject
      console.log('Will try to delete subject ID:', subjectId);
    }
  } catch(e) {
    console.log('Parse error:', e.message);
    return;
  }

  if (!subjectId) {
    console.log('No subject to delete');
    return;
  }

  // Step 3: Try to delete subject
  console.log('\n--- DELETE SUBJECT ---');
  const deleteBody = JSON.stringify({ action: 'delete_subject', subject_id: subjectId });
  await makeRequest({
    hostname: 'localhost',
    port: 8888,
    path: '/api/admin',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      'Content-Length': Buffer.byteLength(deleteBody)
    }
  }, deleteBody);
}

run().catch(console.error);
