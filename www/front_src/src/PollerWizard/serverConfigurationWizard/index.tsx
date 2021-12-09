import * as React from 'react';

import { equals, isNil } from 'ramda';

import Form from '../forms/ServerConfigurationWizardForm';
import { ServerType } from '../models';

interface Props {
  changeServerType: (type: ServerType) => void;
  goToNextStep: () => void;
}

const ServerConfigurationWizard = ({
  changeServerType,
  goToNextStep,
}: Props): JSX.Element => {
  const handleSubmit = ({ server_type }): void => {
    if (isNil(server_type)) {
      return;
    }

    if (equals(server_type, '1')) {
      changeServerType(ServerType.Remote);
    }
    if (equals(server_type, '2')) {
      changeServerType(ServerType.Poller);
    }

    goToNextStep();
  };

  return <Form onSubmit={handleSubmit} />;
};

export default ServerConfigurationWizard;
