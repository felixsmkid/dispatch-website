window.SMCD_PREVIEW = {
    stats: {
        active_calls: 3,
        pending_calls: 2,
        active_pursuits: 1,
        active_bolos: 2,
        online_units: 5,
        lspd_units: 3,
        bcso_units: 2,
        panic_alerts: 1,
    },
    calls: [
        { call_number: '250602-0001', location: 'Legion Square', priority: 'emergency', status: 'active' },
        { call_number: '250602-0002', location: 'Grove Street', priority: 'high', status: 'active' },
        { call_number: '250602-0003', location: 'Vinewood Blvd', priority: 'medium', status: 'pending' },
        { call_number: '250602-0004', location: 'Sandy Shores', priority: 'low', status: 'pending' },
        { call_number: '250602-0005', location: 'Paleto Bay', priority: 'high', status: 'active' },
    ],
    pursuits: [
        { pursuit_code: 'P250602-001', vehicle_description: 'Black Sultan RS', plate: 'SMCD911', current_location: 'Highway 1 North' },
    ],
    bolos: [
        { vehicle: 'White Buffalo', plate: 'ABC123', reason: 'Armed robbery suspect' },
        { vehicle: 'Red Blista', plate: 'XYZ789', reason: 'Hit and run' },
    ],
    panics: [
        { callsign: '1-Adam-12', officer_name: 'Officer Martinez', department: 'LSPD', location: 'Davis Ave & Innocence', created_at: '02 Jun 2026 14:32:10' },
    ],
    units: [
        { department: 'LSPD', callsign: '1-Adam-12', character_name: 'J. Martinez', rank_title: 'Officer', status_code: '10-23', status_label: 'On Scene', last_update: '14:31:05' },
        { department: 'LSPD', callsign: '1-Adam-14', character_name: 'S. Chen', rank_title: 'Officer', status_code: '10-8', status_label: 'Available', last_update: '14:30:22' },
        { department: 'LSPD', callsign: '2-Lincoln-5', character_name: 'M. Brooks', rank_title: 'Sergeant', status_code: '10-57', status_label: 'Pursuit', last_update: '14:32:01' },
        { department: 'BCSO', callsign: '3-County-2', character_name: 'R. Walker', rank_title: 'Deputy', status_code: '10-38', status_label: 'Traffic Stop', last_update: '14:29:44' },
        { department: 'BCSO', callsign: '3-County-7', character_name: 'T. Hayes', rank_title: 'Deputy', status_code: '10-8', status_label: 'Available', last_update: '14:28:15' },
    ],
    officer_calls: [
        { call_number: '250602-0001', location: 'Legion Square', priority: 'emergency', status: 'active' },
    ],
};
