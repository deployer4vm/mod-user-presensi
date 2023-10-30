import globals from "@/globals";

export default {
    // master store  
    // modscsalestoreMasterStore: {
    //     module: "modscsalestoreMasterStore",// nama repo
    //     apiEndpoint: globals().AppConfig.endpoint.api.modscsalestore + '/store/master/store/',// alamat resource api
    //     methods: {
    //         count: {
    //             name: "count",
    //             httpMethod: "GET",
    //             apiEndpoint: globals().AppConfig.endpoint.api.modscsalestore + '/store/master/store/count'
    //         }
    //     }
    // },
    
    // master User -> User Group
    moduserUserGroup: {
        module: "moduserUserGroup",// nama repo
        apiEndpoint: globals().AppConfig.endpoint.api.moduser + '/group/',// alamat resource api
    },
    // master Role -> Role Group
    moduserRoleGroup: {
        module: "moduserRoleGroup",// nama repo
        apiEndpoint: globals().AppConfig.endpoint.api.moduser + '/role/group/',// alamat resource api
    },
    // master Role -> Role Level Group
    moduserRoleLevelGroup: {
        module: "moduserRoleLevelGroup",// nama repo
        apiEndpoint: globals().AppConfig.endpoint.api.moduser + '/role/level-group/',// alamat resource api
    },
};