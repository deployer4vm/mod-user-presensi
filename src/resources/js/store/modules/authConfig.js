import globals from "@/globals";
var apiEndpoint = globals().AppConfig.endpoint.api.moduser + '/auth-config/';

const state = {
    persistant: true,
    dataConfig: {
        password: {
            minlength: 8,
        },
        registration: {
            enable: 0,
            auth_activate: 1,
            admin_send_activation_email: 1,
            tos_confirm: 0,
            default_role_code: 'admin'
        },
        login: {
            rememberme: 0,
            forgotpassword: 0
        },
        otp: {
            enable: 1,
            digit: 8,
            timeout: 5,
            enable_channel: {
                email: 1,
                sms: 0,
                wa: 0
            }
        },
        pin: {
            enable: 1,
            digit: 6
        }
    },
}

const getters = {  
    dataConfig(state) {
        return state.dataConfig;
    },
};
const mutations = { 
    setDataConfig(state, value) {
        state.dataConfig = value;
    },
}

const actions = {
    loadAll({ commit, dispatch, state }) {
        return globals()
            .LocalApi.get(apiEndpoint)
            .then(res => {
                commit('setDataConfig',res.data.data);
                return res.data.data;
            });
    },
    loadRegistration({ commit, dispatch, state }) {
        return globals()
            .LocalApi.get(apiEndpoint + 'registration')
            .then(res => {
                state.dataConfig.registration = res.data.data;
                commit('setDataConfig',state.dataConfig);
                return res.data.data;
            });
    },
    loadLogin({ commit, dispatch, state }) {
        return globals()
            .LocalApi.get(apiEndpoint + 'login')
            .then(res => {
                state.dataConfig.login = res.data.data;
                commit('setDataConfig',state.dataConfig);
                return res.data.data;
            });
    },
    loadPassword({ commit, dispatch, state }) {
        return globals()
            .LocalApi.get(apiEndpoint + 'password')
            .then(res => {
                state.dataConfig.password = res.data.data;
                commit('setDataConfig',state.dataConfig);
                return res.data.data;
            });
    },
    loadOtp({ commit, dispatch, state }) {
        return globals()
            .LocalApi.get(apiEndpoint + 'otp')
            .then(res => {
                state.dataConfig.otp = res.data.data;
                commit('setDataConfig',state.dataConfig);
                return res.data.data;
            });
    },
    loadPin({ commit, dispatch, state }) {
        return globals()
            .LocalApi.get(apiEndpoint + 'pin')
            .then(res => {
                state.dataConfig.pin = res.data.data;
                commit('setDataConfig',state.dataConfig);
                return res.data.data;
            });
    },
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};