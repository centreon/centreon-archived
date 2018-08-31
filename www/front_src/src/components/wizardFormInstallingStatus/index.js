import React from 'react';
import Loader from '../loader';

export default ({formTitle}) => {
  return (
    <div className="form-wrapper installation">
      <div className="form-inner">
        <div className="form-heading">
          <h2 className="form-title">{formTitle}</h2>
        </div>
        <Loader />
        <p className="form-text">Status <span className="form-status valid">[OK]</span></p>
        <p className="form-text">Status <span className="form-status failed">[failed]</span></p>
        <span className="form-error-message">Error message</span>
      </div>
    </div>
  )
}