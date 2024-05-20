import globals from "@/globals";
import { Encryptor } from "node_modules/node-laravel-encryptor";

var apiEndpoint = globals().AppConfig.endpoint.api.moduser + '/authenticator-request/';

var encryptor = new Encryptor({
    key: globals().AppConfig.client.secret_key
});

const state = {
    listRequest: {
        data: [],
        count: 0,
        limit: 10,
        offset: 0,
        currentPage: 1,
        pageCount: 1,
        summary: []
    },
    oneDataRequest: {},
};

const getters = {
    listRequest( state ) {
        return state.listRequest;
    },
    oneDataRequest( state ) {
        return state.oneDataRequest;
    },
};

const mutations = {
    setListRequest( state, data ) {
        state.listRequest = data;
    },
    setDataRequest( state, data ) {
        state.oneDataRequest = data;
    },
};

const actions = { 
    // LIST request
    readListRequest({ commit, dispatch, state }, params={}) {
        return globals()
            .LocalApi.get(apiEndpoint, {
                params: params.params
            })
            .then(res => {
                if(params.saveState==undefined||params.saveState)
                    commit("setListRequest", res.data.data);
                return res.data.data;
            });
    },
    // VERIFY request - get status terakhir
    readOneRequest({ commit }, params) {
        return globals()
            .LocalApi.get(apiEndpoint + params.featureCode + '/' + params.requestCode)
            .then(res => {
                // if(params.saveState==undefined||params.saveState)
                //     commit("setDataRequest", res.data.data);
                return res.data.data.status;
            });
    },
    // CREATE request
    createRequest({ commit, dispatch }, params) {
        return globals()
            .LocalApi.post(apiEndpoint, params.data)
            .then(res => {
                return res.data.data;
            });
    },
    /**
     * GRANT request
     * params
     *      data
     *          auth_note
     *          auth_code   string password/otp/pin
     **/ 
    grantRequest({ commit, dispatch }, params) {

        if(params.data && params.data.auth_code)
            params.data.auth_code = params.data.auth_code?encryptor.encryptSync(params.data.auth_code):params.data.auth_code;

        return globals()
            .LocalApi.post(apiEndpoint + params.featureCode + '/' + params.requestCode, params.data)
            .then(res => {
                return res.data.data;
            });
    },
    /**
     * REJECT request
     * params
     *      data
     *          auth_note
     *          auth_code   string password/otp/pin
     **/ 
    rejectRequest({ commit, dispatch }, params) {

        if(params.data && params.data.auth_code)
            params.data.auth_code = params.data.auth_code?encryptor.encryptSync(params.data.auth_code):params.data.auth_code;

        return globals()
            .LocalApi.delete(apiEndpoint + params.featureCode + '/' + params.requestCode, params.data)
            .then(res => {
                return res.data.data;
            });
    },
};

const data = {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};

export default data;