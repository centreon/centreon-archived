import React from 'react';

import DeleteIcon from '@mui/icons-material/Delete';
import UpdateIcon from '@mui/icons-material/SystemUpdateAlt';
import CheckIcon from '@mui/icons-material/Check';
import InstallIcon from '@mui/icons-material/Add';
import Stack from '@mui/material/Stack';
import { makeStyles } from '@mui/styles';
import {
  Card,
  CardActions,
  CardContent,
  Paper,
  Button,
  Typography,
  Grid,
  Chip,
  LinearProgress,
  Divider,
} from '@mui/material';

import { Entity, ExtensionsStatus, LicenseProps } from '../models';

interface Props {
  deletingEntityId: string | null;
  entities: Array<Entity>;
  installing: ExtensionsStatus;
  onCard: (id: string, type: string) => void;
  onDelete: (id: string, type: string, description: string) => void;
  onInstall: (id: string, type: string) => void;
  onUpdate: (id: string, type: string) => void;
  title: string;
  type: string;
  updating: ExtensionsStatus;
}

const useStyles = makeStyles((theme) => ({
  contentWrapper: {
    [theme.breakpoints.up(767)]: {
      padding: theme.spacing(1.5),
    },
    boxSizing: 'border-box',
    margin: theme.spacing(0, 'auto'),
    padding: theme.spacing(1.5, 2.5, 0, 2.5),
  },
  extensionsTypes: {
    color: theme.palette.text.primary,
  },
}));

const ExtensionsHolder = ({
  title,
  entities,
  onInstall,
  onUpdate,
  onDelete,
  onCard,
  updating,
  installing,
  deletingEntityId,
  type,
}: Props): JSX.Element => {
  const classes = useStyles();

  const parseDescription = (description): string => {
    return description.replace(/^centreon\s+(\w+)/i, (_, $1) => $1);
  };

  const getPropsFromLicense = (licenseInfo): LicenseProps | undefined => {
    if (licenseInfo && licenseInfo.required) {
      if (!licenseInfo.expiration_date) {
        return {
          color: '#f90026',
          label: 'License required',
        };
      }
      if (!Number.isNaN(Date.parse(licenseInfo.expiration_date))) {
        const expirationDate = new Date(licenseInfo.expiration_date);

        return {
          color: '#84BD00',
          label: `License expires ${expirationDate.toISOString().slice(0, 10)}`,
        };
      }

      return {
        color: '#f90026',
        label: 'License not valid',
      };
    }

    return undefined;
  };

  return (
    <div className={classes.contentWrapper}>
      <Stack>
        <Grid
          container
          alignItems="center"
          direction="row"
          spacing={1}
          style={{ marginBottom: 8, width: '100%' }}
        >
          <Grid item>
            <Typography className={classes.extensionsTypes} variant="body1">
              {title}
            </Typography>
          </Grid>
          <Grid item style={{ flexGrow: 1 }}>
            <Divider style={{ backgroundColor: 'rgba(0, 0, 0, 0.12)' }} />
          </Grid>
        </Grid>
        <Grid
          container
          alignItems="stretch"
          spacing={2}
          style={{ cursor: 'pointer' }}
        >
          {entities.map((entity) => {
            const isLoading =
              installing[entity.id] ||
              updating[entity.id] ||
              deletingEntityId === entity.id;

            const licenseInfo = getPropsFromLicense(entity.license);

            return (
              <Grid
                item
                id={`${type}-${entity.id}`}
                key={entity.id}
                style={{ width: 200 }}
                onClick={(): void => {
                  onCard(entity.id, type);
                }}
              >
                <Card
                  style={{ display: 'grid', height: '100%' }}
                  variant="outlined"
                >
                  {isLoading && <LinearProgress />}
                  <CardContent style={{ padding: '10px' }}>
                    <Typography style={{ fontWeight: 'bold' }} variant="body1">
                      {parseDescription(entity.description)}
                    </Typography>
                    <Typography variant="body2">
                      {`by ${entity.label}`}
                    </Typography>
                  </CardContent>
                  <CardActions style={{ justifyContent: 'center' }}>
                    {entity.version.installed ? (
                      <Chip
                        avatar={
                          entity.version.outdated ? (
                            <UpdateIcon
                              style={{
                                color: '#FFFFFF',
                                cursor: 'pointer',
                              }}
                              onClick={(e): void => {
                                e.preventDefault();
                                e.stopPropagation();

                                onUpdate(entity.id, type);
                              }}
                            />
                          ) : (
                            <CheckIcon style={{ color: '#FFFFFF' }} />
                          )
                        }
                        deleteIcon={<DeleteIcon style={{ color: '#FFFFFF' }} />}
                        disabled={isLoading}
                        label={entity.version.current}
                        style={{
                          backgroundColor: entity.version.outdated
                            ? '#FF9A13'
                            : '#84BD00',
                          color: '#FFFFFF',
                        }}
                        onDelete={(): void =>
                          onDelete(entity.id, type, entity.description)
                        }
                      />
                    ) : (
                      <Button
                        color="primary"
                        disabled={isLoading}
                        size="small"
                        startIcon={!entity.version.installed && <InstallIcon />}
                        variant="contained"
                        onClick={(e): void => {
                          e.preventDefault();
                          e.stopPropagation();
                          const { id } = entity;
                          const { version } = entity;
                          if (version.outdated && !updating[entity.id]) {
                            onUpdate(id, type);
                          } else if (
                            !version.installed &&
                            !installing[entity.id]
                          ) {
                            onInstall(id, type);
                          }
                        }}
                      >
                        {entity.version.available}
                      </Button>
                    )}
                  </CardActions>
                  <Paper
                    square
                    elevation={0}
                    style={{
                      alignItems: 'center',
                      ...(licenseInfo?.color && {
                        backgroundColor: licenseInfo.color,
                      }),
                      cursor: 'pointer',
                      display: 'flex',
                      justifyContent: 'center',
                      minHeight: '20px',
                    }}
                  >
                    {licenseInfo?.label && (
                      <Typography style={{ color: '#FFFFFF' }} variant="body2">
                        {licenseInfo.label}
                      </Typography>
                    )}
                  </Paper>
                </Card>
              </Grid>
            );
          })}
        </Grid>
      </Stack>
    </div>
  );
};

export default ExtensionsHolder;
