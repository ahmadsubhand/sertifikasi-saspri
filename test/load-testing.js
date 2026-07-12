import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Trend, Rate, Counter } from 'k6/metrics';

// k6 configuration options
export const options = {
  vus: 100,
  duration: '1m',
  thresholds: {
    http_req_failed: ['rate<0.01'], // Fail test if error rate is greater than 1%
    http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
  },
};

// Base URL for the mobile API
const BASE_URL = __ENV.BASE_URL || 'https://api.digdaya.net/sertifikasi/v1';

// Custom metrics to track response times, throughput, and error rates per API
const apiMetrics = {
  login: {
    duration: new Trend('api_login_duration_ms', true),
    reqs: new Counter('api_login_reqs'),
    errors: new Rate('api_login_errors'),
  },
  me: {
    duration: new Trend('api_me_duration_ms', true),
    reqs: new Counter('api_me_reqs'),
    errors: new Rate('api_me_errors'),
  },
  saspriK: {
    duration: new Trend('api_saspri_k_duration_ms', true),
    reqs: new Counter('api_saspri_k_reqs'),
    errors: new Rate('api_saspri_k_errors'),
  },
  saspriKInfografis: {
    duration: new Trend('api_saspri_k_infografis_duration_ms', true),
    reqs: new Counter('api_saspri_k_infografis_reqs'),
    errors: new Rate('api_saspri_k_infografis_errors'),
  },
  saspriKDetail: {
    duration: new Trend('api_saspri_k_detail_duration_ms', true),
    reqs: new Counter('api_saspri_k_detail_reqs'),
    errors: new Rate('api_saspri_k_detail_errors'),
  },
  saspriKMembers: {
    duration: new Trend('api_saspri_k_members_duration_ms', true),
    reqs: new Counter('api_saspri_k_members_reqs'),
    errors: new Rate('api_saspri_k_members_errors'),
  },
  saspriKCertifications: {
    duration: new Trend('api_saspri_k_certifications_duration_ms', true),
    reqs: new Counter('api_saspri_k_certifications_reqs'),
    errors: new Rate('api_saspri_k_certifications_errors'),
  },
  certificationDetail: {
    duration: new Trend('api_certification_detail_duration_ms', true),
    reqs: new Counter('api_certification_detail_reqs'),
    errors: new Rate('api_certification_detail_errors'),
  },
  certificationDownload: {
    duration: new Trend('api_certification_download_duration_ms', true),
    reqs: new Counter('api_certification_download_reqs'),
    errors: new Rate('api_certification_download_errors'),
  },
  certificationSelfMembers: {
    duration: new Trend('api_certification_self_team_members_duration_ms', true),
    reqs: new Counter('api_certification_self_team_members_reqs'),
    errors: new Rate('api_certification_self_team_members_errors'),
  },
  certificationPeerMembers: {
    duration: new Trend('api_certification_peer_team_members_duration_ms', true),
    reqs: new Counter('api_certification_peer_team_members_reqs'),
    errors: new Rate('api_certification_peer_team_members_errors'),
  },
  notification: {
    duration: new Trend('api_notification_duration_ms', true),
    reqs: new Counter('api_notification_reqs'),
    errors: new Rate('api_notification_errors'),
  },
  logout: {
    duration: new Trend('api_logout_duration_ms', true),
    reqs: new Counter('api_logout_reqs'),
    errors: new Rate('api_logout_errors'),
  },
};

// Helper function to send requests and track metrics
function trackRequest(apiName, method, url, body, params) {
  const metric = apiMetrics[apiName];
  if (!metric) {
    throw new Error(`Metric configuration not found for API: ${apiName}`);
  }

  // Increment throughput counter
  metric.reqs.add(1);

  let res;
  if (method === 'GET') {
    res = http.get(url, params);
  } else if (method === 'POST') {
    res = http.post(url, body, params);
  } else if (method === 'DELETE') {
    res = http.del(url, body, params);
  }

  // Record response time (duration in ms)
  metric.duration.add(res.timings.duration);

  // Record error rate (1 if not 200, 0 otherwise)
  const isError = res.status !== 200;
  metric.errors.add(isError);

  return res;
}

// Generate user list based on console/db/complete_seeder.sql
const users = [];

// // Admins (IDs 1-3)
// users.push('admin.nasional', 'admin.kawasan', 'admin.pusat');

// Independent Users (IDs 4-13)
users.push(
  'bambang.sudjatmiko',
  'siti.nurhaliza',
  'joko.widodo',
  'megawati.soekarno',
  'susilo.yudhoyono',
  'prabowo.subianto',
  'anies.baswedan',
  'ganjar.pranowo',
  'ridwan.kamil',
  'khofifah.parawansa'
);

// Coordinator custom usernames mapped by ID (IDs 14-154)
const coordinatorUsernames = {
  14: 'budiman.sujatmiko',
  34: 'agus.harimurti',
  54: 'erick.thohir',
  74: 'sandiaga.uno',
  94: 'nadiem.makarim',
  114: 'luhut.pandjaitan',
  134: 'mahfud.md',
  139: 'muhaimin.iskandar',
  144: 'ahmad.syaikhu',
  149: 'zulkifli.hasan',
  154: 'suharso.monoarfa'
};

// General members (IDs 14-158)
for (let i = 14; i <= 158; i++) {
  if (coordinatorUsernames[i]) {
    users.push(coordinatorUsernames[i]);
  } else {
    users.push(`user.${i}`);
  }
}

export default function () {
  // Distribute users evenly across all running VUs based on the VU ID
  const userIndex = (__VU - 1) % users.length;
  const username = users[userIndex];
  const password = 'password_0';

  const loginPayload = JSON.stringify({
    username: username,
    password: password,
  });

  const baseHeaders = {
    'Content-Type': 'application/json',
  };

  let token = '';

  // 1. Login Flow (Login => /user/login , /user/me)
  group('01. Login Flow', function () {
    const loginRes = trackRequest('login', 'POST', `${BASE_URL}/user/login`, loginPayload, { headers: baseHeaders });
    const loginSuccess = check(loginRes, {
      'login status is 200': (r) => r.status === 200,
      'has access token': (r) => r.status === 200 && r.json('access_token') !== undefined,
    });

    if (loginSuccess && loginRes.status === 200) {
      token = loginRes.json('access_token');
    }
  });

  // If login failed, skip the rest of the flow for this iteration to avoid cascading errors
  if (!token) {
    sleep(1);
    return;
  }

  const authHeaders = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`,
  };

  group('01. Login - Profile Check', function () {
    const meRes = trackRequest('me', 'GET', `${BASE_URL}/user/me`, null, { headers: authHeaders });
    check(meRes, {
      'profile (me) status is 200': (r) => r.status === 200,
    });
  });

  // 2. Dashboard Flow (Dashboard => /saspri-k , /saspri-k/infografis)
  group('02. Dashboard Flow', function () {
    const saspriKRes = trackRequest('saspriK', 'GET', `${BASE_URL}/saspri-k`, null, { headers: authHeaders });
    const infografisRes = trackRequest('saspriKInfografis', 'GET', `${BASE_URL}/saspri-k/infografis`, null, { headers: authHeaders });

    check(saspriKRes, {
      'saspri-k list status is 200': (r) => r.status === 200,
    });
    check(infografisRes, {
      'infografis status is 200': (r) => r.status === 200,
    });
  });

  // 3. Detail SASPRI Flow (Detail SASPRI => /saspri-k/detail , /saspri-k/members , /saspri-k/certifications)
  group('03. Detail SASPRI Flow', function () {
    const saspriKId = 1;
    const detailRes = trackRequest('saspriKDetail', 'GET', `${BASE_URL}/saspri-k/detail?saspri_k_id=${saspriKId}`, null, { headers: authHeaders });
    const membersRes = trackRequest('saspriKMembers', 'GET', `${BASE_URL}/saspri-k/members?saspri_k_id=${saspriKId}&limit=5&offset=0`, null, { headers: authHeaders });
    const certificationsRes = trackRequest('saspriKCertifications', 'GET', `${BASE_URL}/saspri-k/certifications?saspri_k_id=${saspriKId}&limit=5&offset=0`, null, { headers: authHeaders });

    check(detailRes, {
      'saspri-k detail status is 200': (r) => r.status === 200,
    });
    check(membersRes, {
      'saspri-k members status is 200': (r) => r.status === 200,
    });
    check(certificationsRes, {
      'saspri-k certifications status is 200': (r) => r.status === 200,
    });
  });

  // 4. Detail Sertifikat Flow (Detail Sertifikat => /certification/detail , /certification/download , /certification/self-team-members , /certification/peer-team-members)
  group('04. Detail Sertifikat Flow', function () {
    const certificationId = 11;
    const detailRes = trackRequest('certificationDetail', 'GET', `${BASE_URL}/certification/detail?certification_id=${certificationId}`, null, { headers: authHeaders });
    const downloadRes = trackRequest('certificationDownload', 'GET', `${BASE_URL}/certification/download?certification_id=${1}`, null, { headers: authHeaders }); // sertifikasi yang sudah selesai
    const selfTeamRes = trackRequest('certificationSelfMembers', 'GET', `${BASE_URL}/certification/self-team-members?certification_id=${certificationId}`, null, { headers: authHeaders });
    const peerTeamRes = trackRequest('certificationPeerMembers', 'GET', `${BASE_URL}/certification/peer-team-members?certification_id=${certificationId}`, null, { headers: authHeaders });

    check(detailRes, {
      'certification detail status is 200': (r) => r.status === 200,
    });
    check(downloadRes, {
      'certification download status is 200': (r) => r.status === 200,
    });
    check(selfTeamRes, {
      'certification self-team-members status is 200': (r) => r.status === 200,
    });
    check(peerTeamRes, {
      'certification peer-team-members status is 200': (r) => r.status === 200,
    });
  });

  // 5. Notifikasi Flow (Notifikasi => /notification)
  group('05. Notifikasi Flow', function () {
    const notificationRes = trackRequest('notification', 'GET', `${BASE_URL}/notification?limit=10&offset=0`, null, { headers: authHeaders });
    check(notificationRes, {
      'notification list status is 200': (r) => r.status === 200,
    });
  });

  // 6. Logout Flow (Logout => /logout -> implemented as /user/logout per mobile API spec)
  group('06. Logout Flow', function () {
    const logoutRes = trackRequest('logout', 'POST', `${BASE_URL}/user/logout`, null, { headers: authHeaders });
    check(logoutRes, {
      'logout status is 200': (r) => r.status === 200,
    });
  });

  // Pause for 1 second between iterations to simulate real user typing/reading speed (think time)
  sleep(1);
}
