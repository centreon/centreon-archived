import { useState, useEffect } from 'react';

import { connect } from 'react-redux';

import { postData, useRequest } from '@centreon/ui';

import Form from '../forms/poller/PollerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import { WizardFormProps } from '../models';

const pollerWaitListEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getPollerWaitList';

interface Props
  extends Pick<WizardFormProps, 'goToNextStep' | 'goToPreviousStep'> {
  setWizard: (pollerWizard) => Record<string, unknown>;
}

const FormPollerStepOne = ({
  setWizard,
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const [waitList, setWaitList] = useState<Array<unknown> | null>(null);
  const { sendRequest } = useRequest<Array<unknown>>({
    request: postData,
  });

  const getWaitList = (): void => {
    sendRequest({
      data: null,
      endpoint: pollerWaitListEndpoint,
    })
      .then((data): void => {
        setWaitList(data);
      })
      .catch(() => {
        setWaitList([]);
      });
  };

  useEffect(() => {
    getWaitList();
  }, []);

  const handleSubmit = (data): void => {
    setWizard(data);
    goToNextStep();
  };

  return (
    <Form
      goToPreviousStep={goToPreviousStep}
      initialValues={{}}
      waitList={waitList}
      onSubmit={handleSubmit}
    />
  );
};

const mapDispatchToProps = {
  setWizard: setPollerWizard,
};

const PollerStepOne = connect(null, mapDispatchToProps)(FormPollerStepOne);

export default (
  props: Pick<WizardFormProps, 'goToNextStep' | 'goToPreviousStep'>,
): JSX.Element => {
  return <PollerStepOne {...props} />;
};
