require("dotenv").config();
const {
    Client,
    GatewayIntentBits,
    SlashCommandBuilder,
    Routes,
    REST,
} = require("discord.js");
const axios = require("axios");

const client = new Client({ intents: [GatewayIntentBits.Guilds] });

const commands = [
    new SlashCommandBuilder()
        .setName("register")
        .setDescription("Register as an employee")
        .addStringOption((option) =>
            option
                .setName("fullname")
                .setDescription("Your full name")
                .setRequired(true)
        )
        .addStringOption((option) =>
            option
                .setName("email")
                .setDescription("Your email address")
                .setRequired(true)
        )
        .addStringOption((option) =>
            option
                .setName("position")
                .setDescription("Your job position")
                .setRequired(true)
        ),
    new SlashCommandBuilder()
        .setName("time-in")
        .setDescription("Log your time-in with location"),
    new SlashCommandBuilder()
        .setName("time-out")
        .setDescription("Log your time-out with location"),
].map((command) => command.toJSON());

const rest = new REST({ version: "10" }).setToken(process.env.DISCORD_TOKEN);

client.once("ready", async () => {
    console.log(`✅ Logged in as ${client.user.tag}`);

    try {
        await rest.put(
            Routes.applicationGuildCommands(
                process.env.CLIENT_ID,
                "1184496111579320320"
            ),
            { body: commands }
        );
        console.log("✅ Slash commands registered");
    } catch (error) {
        console.error("❌ Failed to register commands", error);
    }
});

client.on("interactionCreate", async (interaction) => {
    if (!interaction.isChatInputCommand()) return;

    const userId = interaction.user.id;
    const username = interaction.user.username;

    if (interaction.commandName === "register") {
        try {
            await interaction.deferReply({ ephemeral: true });

            // Get all the options from the interaction
            const fullName = interaction.options.getString("fullname");
            const email = interaction.options.getString("email");
            const position = interaction.options.getString("position");

            const response = await axios.post(
                `${process.env.API_URL}/register`,
                {
                    discord_id: userId,
                    name: username,
                    full_name: fullName,
                    email: email,
                    position: position,
                }
            );

            await interaction.editReply(response.data.message);
        } catch (error) {
            let message = "Failed to register.";

            if (error.response && error.response.data) {
                // Log the full error response data for debugging:
                console.log("Full error.response.data:", error.response.data);

                // Check if errors is an object or a string:
                let errors = error.response.data.errors;

                if (typeof errors === "string") {
                    // If errors is stringified JSON, parse it
                    try {
                        errors = JSON.parse(errors);
                    } catch {
                        errors = null;
                    }
                }

                if (errors && typeof errors === "object") {
                    const messages = Object.values(errors).flat().join(", ");
                    message = `❌ Failed to register. ${messages}`;
                } else if (error.response.data.message) {
                    message = `❌ Failed to register. ${error.response.data.message}`;
                }
            } else if (error.message) {
                message = `❌ Failed to register. ${error.message}`;
            }

            console.error(message);
            await interaction.editReply(message);
        }
    } else if (
        interaction.commandName === "time-in" ||
        interaction.commandName === "time-out"
    ) {
        const externalLink = `${process.env.API_URL.replace(
            "/api",
            ""
        )}/location?discord_id=${userId}&action=${interaction.commandName}`;
        await interaction.reply(
            `📍 Click the link below to share your location:\n${externalLink}`
        );
    }
});

client.login(process.env.DISCORD_TOKEN);
