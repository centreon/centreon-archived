import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { useUpdateAtom, useAtomValue } from 'jotai/utils';

import { Typography, Button, FormControlLabel, Checkbox } from '@mui/material';

import {
  postData,
  useRequest,
  MultiAutocompleteField,
  SelectField,
  SelectEntry,
} from '@centreon/ui';

import { pollerAtom, setWizardDerivedAtom } from '../PollerAtoms';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';
import routeMap from '../../reactRoutes/routeMap';

const getPollersEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getRemotesList';
const wizardFormEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';
interface Props {
  goToNextStep: () => void;
  goToPreviousStep: () => void;
}
interface PollerData {
  centreon_central_ip?: string;
  linked_remote_master?: string;
  linked_remote_slaves?: Array<string>;
  open_broker_flow?: boolean;
  server_ip?: string;
  server_name?: string;
  server_type?: string;
  submitStatus?: boolean;
}

interface Poller {
  id: string;
  ip: string;
  name: string;
}

interface stepTwoFormData {
  linked_remote_master: string;
  linked_remote_slaves: Array<SelectEntry>;
  open_broker_flow: boolean;
}
const FormPollerStepTwo = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [pollers, setPollers] = React.useState<Array<Poller>>([]);
  const [stepTwoFormData, setStepTwoFormData] = React.useState<stepTwoFormData>(
    {
      linked_remote_master: '',
      linked_remote_slaves: [],
      open_broker_flow: false,
    },
  );

  const { sendRequest: getPollersRequest } = useRequest<Array<Poller>>({
    request: postData,
  });
  const { sendRequest: postWizardFormRequest } = useRequest<{
    success: boolean;
  }>({
    request: postData,
  });
  const pollerData = useAtomValue<PollerData>(pollerAtom);
  const setWizard = useUpdateAtom(setWizardDerivedAtom);

  React.useEffect(() => {
    getPollers();
  }, []);

  const navigate = useNavigate();

  const getPollers = (): void => {
    getPollersRequest({ data: null, endpoint: getPollersEndpoint }).then(
      setPollers,
    );
  };

  const handleChange = (evt): void => {
    const { value, name } = evt.target;

    if (name === 'open_broker_flow') {
      setStepTwoFormData({
        ...stepTwoFormData,
        open_broker_flow: !stepTwoFormData.open_broker_flow,
      });

      return;
    }
    setStepTwoFormData({
      ...stepTwoFormData,
      [name]: value,
    });
  };

  const changeValue = (_, slaves): void => {
    setStepTwoFormData({
      ...stepTwoFormData,
      linked_remote_slaves: slaves,
    });
  };

  const handleSubmit = (event): void => {
    event.preventDefault();
    const data = {
      ...stepTwoFormData,
      linked_remote_slaves: stepTwoFormData.linked_remote_slaves.map(
        ({ id }) => id,
      ),
    };
    const dataToPost = { ...data, ...pollerData };
    dataToPost.server_type = 'poller';

    postWizardFormRequest({
      data: dataToPost,
      endpoint: wizardFormEndpoint,
    })
      .then(({ success }) => {
        setWizard({ submitStatus: success });
        if (pollerData.linked_remote_master) {
          goToNextStep();
        } else {
          navigate(routeMap.pollerList);
        }
      })
      .catch(() => undefined);
  };

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">
          {t('Add advanced server configuration')}
        </Typography>
      </div>
      <form onSubmit={handleSubmit}>
        <div className={classes.form}>
          {pollers.length !== 0 && (
            <SelectField
              fullWidth
              label={t('Attach poller to a master remote server')}
              name="linked_remote_master"
              options={pollers.map((c) => ({
                id: c.id,
                name: c.name,
              }))}
              selectedOptionId={stepTwoFormData.linked_remote_master}
              onChange={handleChange}
            />
          )}
          {stepTwoFormData.linked_remote_master && pollers.length >= 2 && (
            <MultiAutocompleteField
              fullWidth
              label={t('Attach poller to a slave remote server')}
              options={pollers
                .filter(
                  (poller) =>
                    poller.id !== stepTwoFormData.linked_remote_master,
                )
                .map((c) => ({
                  id: c.id,
                  name: c.name,
                }))}
              value={stepTwoFormData.linked_remote_slaves}
              onChange={changeValue}
            />
          )}
          <FormControlLabel
            control={
              <Checkbox
                checked={stepTwoFormData.open_broker_flow}
                name="open_broker_flow"
                onChange={handleChange}
              />
            }
            label={`${t(
              'Advanced: reverse Centreon Broker communication flow',
            )}`}
          />
          <div className={classes.formButton}>
            <Button size="small" type="button" onClick={goToPreviousStep}>
              {t('Previous')}
            </Button>
            <Button
              color="primary"
              size="small"
              type="submit"
              variant="contained"
            >
              {t('Apply')}
            </Button>
          </div>
        </div>
      </form>
    </div>
  );
};

export default FormPollerStepTwo;
