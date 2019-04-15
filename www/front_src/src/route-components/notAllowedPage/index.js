import React from 'react';
import { connect } from 'react-redux';
import styles from '../../styles/partials/_messages.scss';

const notAllowedPage = ({ fetched }) => (
  <>
    {fetched &&
      <div className={styles["message-alert"]}>
        You are not allowed to see this page
      </div>
    }
  </>
);

const mapStateToProps = ({ externalComponents }) => ({
  fetched: externalComponents.fetched
});

export default connect(mapStateToProps)(notAllowedPage);
