import React from "react";
import Loader from "../loader";

export default ({
  formTitle,
  statusCreating,
  statusGenerating,
  statusProcessing,
  error
}) => {
  return (
    <div className="form-wrapper installation">
      <div className="form-inner">
        <div className="form-heading">
          <h2 className="form-title">{formTitle}</h2>
        </div>
        <Loader />
        <p className="form-text">
          Creating Export Task{" "}
          <span
            className={"form-status" + (statusCreating ? " valid" : " failed")}
          >
            {statusCreating != null ? (
              <span>{statusCreating ? "[OK]" : "[FAIL]"}</span>
            ) : (
              "..."
            )}
          </span>
        </p>
        <p className="form-text">
          Generating Export Files{" "}
          <span
            className={
              "form-status" + (statusGenerating ? " valid" : " failed")
            }
          >
            {statusGenerating != null ? (
              <span>{statusGenerating ? "[OK]" : "[FAIL]"}</span>
            ) : (
              "..."
            )}
          </span>
        </p>
        <p className="form-text">
          Processing Remote Import/Configuration{" "}
          <span
            className={
              "form-status" + (statusProcessing ? " valid" : " failed")
            }
          >
            {statusProcessing != null ? (
              <span>{statusProcessing ? "[OK]" : "[FAIL]"}</span>
            ) : (
              "..."
            )}
          </span>
        </p>
        {error ? <span className="form-error-message">{error}</span> : null}
      </div>
    </div>
  );
};
