<template>
    <!-- <b-input-group :append="dataCount"> -->
        <v-single-select 
            selectLabel="" deselectLabel="" 
            :modelData="selectedUserId"
            @onSelect="selectUser($event)"
            @onSearhChange="loadUser"
            :ajaxSearch="true"
            :options="userListSelect"
            :isLoading="isLoading"
            :placeholder="placeholder?placeholder:'Cari user untuk dipilih...'"
            :noOptions="noOptions?noOptions:'Cari user untuk dipilih...'"
            :disabled="disabled" 
        />        
    <!-- </b-input-group> -->
</template>

<script>
    export default {
        name: "v-select-user",
        props: [
            'placeholder',
            'noOptions',
            'modelData',
            'disabled',
            'params'
        ], 
        data() {
            return {   
                authAccess:{},
                defaultFullAccess: {
                    c: 1, r:1, u:1, d:1
                },   
                userListSelect:[],
                //--browse product var  
                selectedUserId: 0,
                isLoading: false,      
                selectUserSelected: false,
                firstLoad: true,
                dataCount: ''
            };
        },       
        watch: {
            'modelData': function(v) {   
                if(this.selectedUserId != v){          
                    if(this.userListSelect.length <= 0){
                        this.loadOneUser(this.modelData);
                    }else{                        
                        this.selectUser(this.modelData);
                    }
                }
            },
        },
        mounted() {
            // console.log('Component v-select-user mounted.')
        },
        created() {   
            if(this.modelData && this.disabled){
                this.loadOneUser(this.modelData);
            }else{
                // this.loadUser('',5);
            }
        },
        methods: {    
            loadUser(q='',limit=15){
                if(q!='' || this.selectUserSelected == false){
                    // let val = q.toLowerCase();      
                    var that = this;
                    clearTimeout(this.suggestTimeout);
                    this.suggestTimeout = setTimeout(function(){
                        that.selectUserSelected = false;
                        that.loadUserDb(q,limit);      
                    },300);
                }  

            },
            loadUserDb(q='',limit){
                this.isLoading = true;
                var params = this.params?this.params:{};

                params.q = q;

                if(params.limit == undefined)
                    params.limit = limit;
                
                this.$store.dispatch("user/userList",{
                        saveState:false,
                        params: params
                    })
                    .then((res)=>{
                        this.isLoading = false;
                        this.userListSelect = [];
                        res.data.forEach(el => {
                            this.userListSelect.push({
                                text: `${el.name} - ${el.role}`,
                                value: el.id
                            });
                        });
                        if(res.count>params.limit)
                            this.userListSelect.push({
                                text: '...ada data lainnya (' + (res.count-params.limit)+ ' data), ketikan query yang lebih lengkap lagi',
                                value: 'novalue',
                                disabled: true,
                                $isDisabled: true
                            });

                        this.dataCount = res.count?res.count.toString():'';

                        if(this.firstLoad && this.modelData)
                            this.selectUser(this.modelData);
                        this.firstLoad = false;
                    })
                    .catch((res) => {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.warning_title"),
                            text: this.Trans.get("alert.read_failed", { attribute: this.Trans.get("pos.product") }) + "<br>\n" + res.message,
                            type: "warning",
                        });
                    });
            },
            loadOneUser(userId){
                this.$store.dispatch("user/readOneUser",{saveState:false,id:userId})
                    .then((res)=>{
                        this.userListSelect = [];                        
                        this.userListSelect.push({
                            text: `${res.name} - ${res.role}`,
                            value: res.id
                        });
                        if(this.firstLoad && this.modelData)
                            this.selectUser(this.modelData);
                    })
                    .catch((res) => {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.warning_title"),
                            text: this.Trans.get("alert.read_failed", { attribute: this.Trans.get("pos.product") }) + "<br>\n" + res.message,
                            type: "warning",
                        });
                    });
            },
            selectUser(userId)
            {
                this.selectUserSelected = true;
                this.selectedUserId = userId; 
                this.$emit('onSelect', userId);
            },
        }
        
    }
</script>
