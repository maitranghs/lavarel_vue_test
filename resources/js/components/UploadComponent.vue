<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Upload File Form</div>

                    <div class="card-body">
                        <div v-if="errors" class="invalid-feedback" role="alert">
                            <ul class="list-unstyled"><li v-for="err in errors" :key="err"><strong>{{ err }}</strong></li></ul>
                        </div>
                        <div class="row">
                            <p>
                                <label for="file">File: </label>
                                <input type="file" id="file" ref="file" v-on:change="changeUploadFile()"/>
                            </p>
                        </div>
                        <div class="row">
                            <p>
                                <label for="saveToDB">Save to Database: </label>
                                <input type="checkbox" id="saveToDB" v-model="saveToDB"/>
                            </p>
                        </div>
                        <button v-on:click="submitForm()">Submit</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="summaryData">
            <div class="row justify-content-center">
                <div class="card-header">Summarized Data</div>
                    
                <div class="card-body">
                    <p><strong>Avg price: </strong> {{ summaryData.avgPrice }}</p>
                    <p><strong>Total houses sold: </strong> {{ summaryData.totalHousesSold }}</p>
                    <p><strong>No of crimes in 2011: </strong> {{ summaryData.noOfCrimes }}</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="card-header">Average price per year in the London area</div>

                <div class="card-body">
                    <p v-for="({ avg }, key) in summaryData.avgPricePerYear" :key="key">
                        <strong>{{ key }}</strong> : {{ avg }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
            return {
                errors: [],
                file: '',
                saveToDB: null,
                summaryData: null
            };
        },
        methods: {
            validateForm: function() {
                this.errors = [];
                if(!this.file) {
                    this.errors.push('Please choose a file.');
                }
                return this.errors.length === 0;
            },
            changeUploadFile: function() {
                this.file = this.$refs.file.files[0]
            },
            submitForm: async function() {

                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('saveToDB', this.saveToDB);

                if (this.validateForm()) {
                    let responseData = await window.axios.post('/upload', formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    this.errors = responseData.data.errors;
                    this.summaryData = responseData.data.data;
                }
            }
        }
        
    }
</script>
