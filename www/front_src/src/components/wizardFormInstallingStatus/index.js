import React from "react";
import Loader from "../loader";
import {Translate} from 'react-redux-i18n';

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
          <Translate value="Creating Export Task"/>{" "}
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
          <Translate value="Generating Export Files"/>{" "}
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
          <Translate value="Processing Remote Import/Configuration"/>{" "}
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
