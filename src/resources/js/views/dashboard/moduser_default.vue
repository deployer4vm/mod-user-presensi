<template>  
    <div>
        <template v-for="(vContent, iContent) in contentDashboard.content">
            <div v-if="vContent.content.length" :key="'row-dashboard-' + iContent" class="row">
                <template v-for="(featureData, featureIdx) in vContent.content">
                    <div :key="'item-dashboard-' + iContent + '-' + featureIdx" class="col-md my-2">
                        <component v-bind:is="featureList[featureData.feature]"/>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>
<script>
export default {
    name: "moduser-dashboard-default",
    props:[
        'featureList'
    ],
    data: () => ({

    }),
    computed: {
        // shared view data
        viewData: {
            get() {
                return this.$store.state.moduserView.dataDashboard;
            },
            set(value) {
                this.$store.commit("moduserView/setDataDashboard", value);
            }
        },
        contentDashboard(){
            return this.UserAuth.getAuthRole()['dashboard']['content'];
        },
        configDashboard(){
            return this.AppConfig.packageLocal.moduser.dashboard.template.moduser_default.config;
        }
    },
    created() {
        
    }
}
</script>