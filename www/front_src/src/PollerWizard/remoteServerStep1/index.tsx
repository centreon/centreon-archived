import * as React from 'react';

import { connect } from 'react-redux';

import { postData, useRequest } from '@centreon/ui';

import Form from '../forms/remoteServer/RemoteServerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import { WizardFormProps } from '../models';

const remoteServerWaitListEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getWaitList';

interface Props
  extends Pick<WizardFormProps, 'goToPreviousStep' | 'goToNextStep'> {
  pollerData: Record<string, unknown>;
  setWizard: (pollerWizard) => Record<string, unknown>;
}

const FormRemoteServerStepOne = ({
  setWizard,
  pollerData,
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const [waitList, setWaitList] = React.useState<Array<unknown> | null>(null);
  const { sendRequest } = useRequest<Array<unknown>>({
    request: postData,
  });

  const getWaitList = (): void => {
    sendRequest({
      data: null,
      endpoint: remoteServerWaitListEndpoint,
    })
      .then((data): void => {
        setWaitList(data);
      })
      .catch(() => {
        setWaitList([]);
      });
  };

  React.useEffect(() => {
    getWaitList();
  }, []);

  const handleSubmit = (data): void => {
    setWizard(data);
    goToNextStep();
  };

  return (
    <Form
      goToPreviousStep={goToPreviousStep}
      initialValues={{ ...pollerData, centreon_folder: '/centreon/' }}
      waitList={waitList}
      onSubmit={handleSubmit}
    />
  );
};

const mapStateToProps = ({ pollerForm }): Pick<Props, 'pollerData'> => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setWizard: setPollerWizard,
};

const RemoteServerStepOne = connect(
  mapStateToProps,
  mapDispatchToProps,
)(FormRemoteServerStepOne);

export default (
  props: Pick<WizardFormProps, 'goToNextStep' | 'goToPreviousStep'>,
): JSX.Element => {
  return <RemoteServerStepOne {...props} />;
};
