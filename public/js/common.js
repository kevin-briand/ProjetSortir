
class EZAjax {
    /**
     *
     * @param errorCallback
     * @param successCallback
     * @param requestUrl
     * @param data
     * @param ajaxOptions
     */
    constructor(errorCallback, successCallback, requestUrl, data, ajaxOptions = {}) {

        this.ajaxObj = null;
        this.ajaxOptionsObj = ajaxOptions;
        this.attempts = 0;
        this.delay = 300;
        this.errorCallback = errorCallback;
        this.successCallback = successCallback;
        //add the settings to the ajaxOptions
        this.ajaxOptionsObj.url = requestUrl;
        this.ajaxOptionsObj.data = data;
        this.ajaxOptionsObj.error = this.ajaxError.bind(this);
        this.ajaxOptionsObj.success = this.ajaxSuccess.bind(this);

    }

    ajaxError(jqXHR, errorString, errorThrown) {
        console.log('error string ' + errorString + ' error thrown ' + errorThrown);
        if (this.attempts <= 3) {
            console.log('attempts is ' + this.attempts + ' delay ' + this.delay);
            setTimeout(() => {
                this.performRequest();
            }, this.delay *= 2);
        } else {
            this.errorCallback(jqXHR, errorString, errorThrown);
        }
    }

    ajaxSuccess(response) {
        this.successCallback(response);
    }

    performRequest() {

        this.attempts++;
        console.log('performing request ' + this.attempts);
        //prevent multiple requests
        if (this.ajaxObj) {
            this.ajaxObj.abort();
        }
        console.log('the ajax options ' + JSON.stringify(this.ajaxOptionsObj));
        this.ajaxObj = $.ajax(this.ajaxOptionsObj);
    }

}