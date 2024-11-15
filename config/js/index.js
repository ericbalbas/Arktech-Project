class Ajax {
  constructor() {
    this.url = "";
    this.data = null; // Changed from array to null to handle different types of responses
    this.error = null;
    this.method = "GET";
    this.contentType = "application/json";
    this.body = null;
    this.responseType = "json"; // Default response type
  }

  setUrl(url) {
    this.url = url;
    return this; // Enable chaining
  }

  setMethod(method) {
    this.method = method.toUpperCase();
    return this; // Enable chaining
  }

  setContentType(contentType) {
    this.contentType = contentType;
    return this; // Enable chaining
  }

  setBody(body) {
    this.body = body;
    return this; // Enable chaining
  }

  setResponseType(responseType) {
    this.responseType = responseType; // Set the desired response type
    return this; // Enable chaining
  }

  async send() {
    try {
      const response = await fetch(this.url, {
        method: this.method,
        headers: {
          "Content-Type": this.contentType,
        },
        body:
          this.method !== "GET" && this.body
            ? this.contentType === "application/json"
              ? JSON.stringify(this.body)
              : this.body
            : null,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      // Handle different response types
      switch (this.responseType) {
        case "json":
          this.data = await response.json();
          break;
        case "text":
          this.data = await response.text();
          break;
        case "blob":
          this.data = await response.blob();
          break;
        case "formData":
          this.data = await response.formData();
          break;
        default:
          this.data = await response.text(); // Default to text if unknown responseType
      }
    } catch (error) {
      this.error = error;
    }
    return this; // Enable chaining
  }

  getData() {
    return this.data;
  }

  reset() {
    this.url = "";
    this.data = null; // Reset to null for different response types
    this.error = null;
    this.method = "GET";
    this.contentType = "application/json";
    this.body = null;
    this.responseType = "json"; // Reset response type to default
    return this;
  }

  getError() {
    return this.error ? `${this.error} fix this!` : null;
  }
}
