import React from 'react';

export default ({formTitle}) => (
  <div className="form-wrapper">
    <div className="form-inner">
      <div className="form-heading">
        <h2 className="form-title">{formTitle}</h2>
      </div>
      <div>Loader...</div>
      <p className="form-text">Status <span className="form-status valid">[OK]</span></p>
      <p className="form-text">Status <span className="form-status failed">[failed]</span></p>
      <span className="form-error-message">Error message</span>
    </div>
  </div>
)